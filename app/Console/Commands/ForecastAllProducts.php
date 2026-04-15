<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Produk;
use App\Models\ModelHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ForecastAllProducts extends Command
{
    protected $signature = 'app:forecast-all
        {--model=prophet : AI model to use (lstm or prophet)}
        {--force : Force forecast even if recently calculated}';

    protected $description = 'Fast inference-only batch forecast using active model versions';

    private const FLASK_BASE_URL = 'http://127.0.0.1:5000';
    private const TIMEOUT_SECONDS = 120;
    private const CHUNK_SIZE = 50;

    public function handle()
    {
        $startTime = now();
        $model = $this->option('model');
        $force = $this->option('force');

        if (!in_array($model, ['lstm', 'prophet'])) {
            $this->error("Invalid model. Use 'lstm' or 'prophet'");
            return Command::FAILURE;
        }

        $this->info("🚀 Starting batch forecast with {$model} model...");
        $this->newLine();

        // ── Step 1: Check Flask server ──
        $flaskAvailable = $this->checkFlaskServer();

        if (!$flaskAvailable) {
            $this->error('Flask server is not available. Fast prediction requires AI server online.');
            return Command::FAILURE;
        }

        // ── Step 3: Predict for each product ──
        $this->info("📦 Step 2: Predicting for all products...");

        $query = Produk::query();

        if (!$force) {
            $query->where(function ($q) {
                $q->whereNull('last_forecast_at')
                    ->orWhere('last_forecast_at', '<', now()->subDays(7));
            });
        }

        $totalProducts = $query->count();

        if ($totalProducts === 0) {
            $this->info('✅ All products have recent forecasts. Use --force to recalculate.');
            return Command::SUCCESS;
        }

        $this->info("   Processing {$totalProducts} products...");

        $progressBar = $this->output->createProgressBar($totalProducts);
        $progressBar->start();

        $successCount = 0;
        $failCount = 0;
        $skippedCount = 0;
        $skippedNoVersionCount = 0;
        $failedProducts = [];
        $details = [];
        $successMaeTotal = 0.0;
        $successRmseTotal = 0.0;
        $successWmapeTotal = 0.0;
        $successMetricCount = 0;

        $query->chunk(self::CHUNK_SIZE, function ($products) use (
            $model,
            &$successCount,
            &$failCount,
            &$skippedCount,
            &$skippedNoVersionCount,
            &$failedProducts,
            &$details,
            &$successMaeTotal,
            &$successRmseTotal,
            &$successWmapeTotal,
            &$successMetricCount,
            $progressBar
        ) {
            foreach ($products as $product) {
                try {
                    $activeModel = ModelHistory::query()
                        ->where('id_roster', $product->IdRoster)
                        ->where('model_type', $model)
                        ->where('is_active', true)
                        ->first();

                    if (!$activeModel) {
                        $details[] = [
                            'id_roster' => $product->IdRoster,
                            'nama_produk' => $product->NamaProduk,
                            'forecasted_demand' => null,
                            'wmape_score' => null,
                            'mae_score' => null,
                            'rmse_score' => null,
                        ];
                        $skippedCount++;
                        $skippedNoVersionCount++;
                        $progressBar->advance();
                        continue;
                    }

                    $dataType = ucfirst(strtolower(trim((string) ($activeModel->data_type ?? ''))));

                    $salesData = $this->getSalesHistory($product->IdRoster, $dataType);

                    if ($salesData['bulan']->isEmpty() || $salesData['bulan']->count() < 3) {
                        Log::warning('Forecast skipped due to insufficient filtered sales history', [
                            'id_roster' => $product->IdRoster,
                            'framework' => $model,
                            'model_version' => $activeModel->version_id,
                            'data_type' => $dataType,
                            'points' => $salesData['bulan']->count(),
                        ]);

                        $details[] = [
                            'id_roster' => $product->IdRoster,
                            'nama_produk' => $product->NamaProduk,
                            'forecasted_demand' => null,
                            'wmape_score' => null,
                            'mae_score' => null,
                            'rmse_score' => null,
                            'data_type' => $dataType,
                            'reason' => 'insufficient_filtered_data',
                        ];
                        $failCount++;
                        $failedProducts[] = $product->IdRoster;
                        $progressBar->advance();
                        continue;
                    }

                    Log::info('Batch forecast model selection', [
                        'id_roster' => $product->IdRoster,
                        'framework' => $model,
                        'model_version' => $activeModel->version_id,
                        'data_type' => $dataType,
                    ]);

                    $predictionResult = $this->callFlaskPredict($model, $salesData, $product->IdRoster, $activeModel->version_id);
                    $forecastModel = $model;

                    if ($predictionResult === null) {
                        $details[] = [
                            'id_roster' => $product->IdRoster,
                            'nama_produk' => $product->NamaProduk,
                            'forecasted_demand' => null,
                            'wmape_score' => null,
                            'mae_score' => null,
                            'rmse_score' => null,
                            'data_type' => $dataType,
                            'reason' => 'prediction_failed',
                        ];
                        $failCount++;
                        $failedProducts[] = $product->IdRoster;
                        $progressBar->advance();
                        continue;
                    }

                    $forecast = $predictionResult['forecast'];
                    $maeScore = $predictionResult['mae'];
                    $rmseScore = $predictionResult['rmse'];
                    $wmapeScore = $predictionResult['wmape'];

                    $status = $this->calculateStatus(
                        $product->stock ?? 0,
                        $forecast,
                        $product->safety_stock ?? 70
                    );

                    $product->update([
                        'forecasted_demand' => round($forecast, 2),
                        'forecast_model' => $forecastModel,
                        'forecast_status' => $status,
                        'last_forecast_at' => now(),
                    ]);

                    $details[] = [
                        'id_roster' => $product->IdRoster,
                        'nama_produk' => $product->NamaProduk,
                        'forecasted_demand' => round($forecast, 2),
                        'wmape_score' => $wmapeScore,
                        'mae_score' => $maeScore,
                        'rmse_score' => $rmseScore,
                        'data_type' => $dataType,
                        'reason' => null,
                    ];

                    if ($maeScore !== null && $rmseScore !== null && $wmapeScore !== null) {
                        $successMaeTotal += (float) $maeScore;
                        $successRmseTotal += (float) $rmseScore;
                        $successWmapeTotal += (float) $wmapeScore;
                        $successMetricCount++;
                    }

                    $successCount++;
                } catch (\Exception $e) {
                    Log::error("Forecast failed for product {$product->IdRoster}", [
                        'error' => $e->getMessage()
                    ]);
                    $details[] = [
                        'id_roster' => $product->IdRoster,
                        'nama_produk' => $product->NamaProduk,
                        'forecasted_demand' => null,
                        'wmape_score' => null,
                        'mae_score' => null,
                        'rmse_score' => null,
                        'data_type' => isset($activeModel) ? ucfirst(strtolower(trim((string) ($activeModel->data_type ?? '')))) : null,
                        'reason' => 'exception',
                    ];
                    $failCount++;
                    $failedProducts[] = $product->IdRoster;
                }

                $progressBar->advance();
            }
        });

        $progressBar->finish();
        $this->newLine(2);

        $duration = $startTime->diffInSeconds(now());
        $this->info("✅ Forecast complete!");
        $this->table(
            ['Metric', 'Value'],
            [
                ['Success', $successCount],
                ['Failed', $failCount],
                ['Skipped (no data)', $skippedCount],
                ['Skipped (no active version)', $skippedNoVersionCount],
                ['Duration', "{$duration}s"],
                ['Model Used', strtoupper($model)]
            ]
        );

        $this->line('SUMMARY: ' . json_encode([
            'success' => $successCount,
            'failed' => $failCount,
            'skipped' => $skippedCount,
            'skipped_no_version' => $skippedNoVersionCount,
            'total' => $totalProducts,
            'failed_products' => $failedProducts,
            'model' => strtoupper($model),
            'metrics' => [
                'mae' => $successMetricCount > 0 ? round($successMaeTotal / $successMetricCount, 4) : null,
                'rmse' => $successMetricCount > 0 ? round($successRmseTotal / $successMetricCount, 4) : null,
                'wmape' => $successMetricCount > 0 ? round($successWmapeTotal / $successMetricCount, 4) : null,
            ],
            'details' => $details,
        ], JSON_UNESCAPED_UNICODE));

        $this->showStatusBreakdown();
        return Command::SUCCESS;
    }

    /**
     * Check Flask server health
     */
    private function checkFlaskServer(): bool
    {
        try {
            $response = Http::timeout(5)->get(self::FLASK_BASE_URL . '/health');
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get sales history for a single product (last 12 months)
     */
    private function getSalesHistory(string $idRoster, ?string $dataType = null): array
    {
        $query = DB::table('detail_transaksi')
            ->join('transaksi', 'detail_transaksi.IdTransaksi', '=', 'transaksi.IdTransaksi')
            ->select(
                DB::raw($this->monthExpression() . ' as bulan'),
                DB::raw('SUM(detail_transaksi.QtyProduk) as terjual')
            )
            ->where('detail_transaksi.IdRoster', $idRoster)
            ->where('transaksi.tglTransaksi', '>=', Carbon::now()->subMonths(12));

        $normalizedDataType = ucfirst(strtolower(trim((string) $dataType)));
        if (in_array($normalizedDataType, ['Eceran', 'Borongan'], true)) {
            $query->where('detail_transaksi.data_type', $normalizedDataType);
        }

        $salesData = $query->groupBy('bulan')->orderBy('bulan')->get();

        return [
            'bulan' => $salesData->pluck('bulan'),
            'terjual' => $salesData->pluck('terjual')
        ];
    }

    /**
     * Return a month expression compatible with both sqlite tests and MySQL production.
     */
    private function monthExpression(): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', transaksi.tglTransaksi)"
            : "DATE_FORMAT(transaksi.tglTransaksi, '%Y-%m')";
    }

    /**
     * Call Flask predict endpoint (uses pre-trained model)
     */
    private function callFlaskPredict(string $model, array $salesData, string $idRoster, string $modelVersion): ?array
    {
        try {
            $endpoint = $model === 'lstm' ? '/predictlstm' : '/predictprophet';

            $payload = [
                'bulan' => $salesData['bulan']->toArray(),
                'terjual' => $salesData['terjual']->toArray(),
                'model_version' => $modelVersion,
            ];

            Log::debug('Batch forecast payload', [
                'id_roster' => $idRoster,
                'endpoint' => $endpoint,
                'payload' => [
                    'bulan_count' => count($payload['bulan']),
                    'terjual_count' => count($payload['terjual']),
                    'model_version' => $payload['model_version'],
                ],
            ]);

            $response = Http::timeout(self::TIMEOUT_SECONDS)
                ->asJson()
                ->post(self::FLASK_BASE_URL . $endpoint, $payload);

            if ($response->successful()) {
                $result = $response->json();

                if (!is_array($result)) {
                    Log::warning('Flask predict returned non-array payload', [
                        'id_roster' => $idRoster,
                        'framework' => $model,
                    ]);
                    return null;
                }

                $forecastValue = $result['forecast'][0] ?? null;

                if ($forecastValue === null) {
                    Log::warning('Flask predict payload missing forecast value', [
                        'id_roster' => $idRoster,
                        'framework' => $model,
                        'payload' => $result,
                    ]);
                    return null;
                }

                Log::info('Batch forecast model selection', [
                    'id_roster' => $idRoster,
                    'framework' => $model,
                    'model_version' => $modelVersion,
                    'model_name' => $result['model']['model_name'] ?? null,
                ]);

                $metrics = is_array($result['metrics'] ?? null) ? $result['metrics'] : [];

                return [
                    'forecast' => (float) $forecastValue,
                    'mae' => isset($metrics['mae']) ? (float) $metrics['mae'] : null,
                    'rmse' => isset($metrics['rmse']) ? (float) $metrics['rmse'] : null,
                    'wmape' => array_key_exists('wmape', $metrics) && $metrics['wmape'] !== null
                        ? (float) $metrics['wmape']
                        : null,
                ];
            }

            Log::warning('Flask predict returned non-success status', [
                'id_roster' => $idRoster,
                'endpoint' => $endpoint,
                'framework' => $model,
                'model_version' => $modelVersion,
                'status' => $response->status(),
                'error' => $response->json('error') ?? $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::warning('Flask predict failed', [
                'id_roster' => $idRoster,
                'framework' => $model,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Calculate stock status
     */
    private function calculateStatus(int $currentStock, float $forecast, int $safetyStock): string
    {
        if ($currentStock < $safetyStock) {
            return 'critical';
        }
        if ($currentStock < ($forecast + $safetyStock)) {
            return 'low';
        }
        if ($currentStock > (($forecast + $safetyStock) * 3)) {
            return 'overstock';
        }
        return 'safe';
    }

    /**
     * Show status breakdown
     */
    private function showStatusBreakdown(): void
    {
        $this->newLine();
        $this->info('📊 Stock Status Breakdown:');

        $breakdown = Produk::select('forecast_status', DB::raw('count(*) as count'))
            ->whereNotNull('forecast_status')
            ->groupBy('forecast_status')
            ->get();

        $tableData = [];
        foreach ($breakdown as $item) {
            $emoji = match ($item->forecast_status) {
                'critical' => '🔴',
                'low' => '🟡',
                'safe' => '🟢',
                'overstock' => '🟠',
                default => '⚪'
            };

            $tableData[] = [
                $emoji . ' ' . ucfirst($item->forecast_status),
                $item->count
            ];
        }

        $this->table(['Status', 'Count'], $tableData);
    }
}
