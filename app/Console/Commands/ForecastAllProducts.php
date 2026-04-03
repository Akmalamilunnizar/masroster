<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Produk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class ForecastAllProducts extends Command
{
    protected $signature = 'app:forecast-all
        {--model=prophet : AI model to use (lstm or prophet)}
        {--force : Force forecast even if recently calculated}
        {--skip-train : Skip training, use existing saved model}';

    protected $description = 'Train AI model and batch forecast all products';

    private const FLASK_BASE_URL = 'http://127.0.0.1:5000';
    private const TIMEOUT_SECONDS = 120;
    private const CHUNK_SIZE = 50;

    public function handle()
    {
        $startTime = now();
        $model = $this->option('model');
         $force = $this->option('force');
        $skipTrain = $this->option('skip-train');

        if (!in_array($model, ['lstm', 'prophet'])) {
            $this->error("Invalid model. Use 'lstm' or 'prophet'");
            return Command::FAILURE;
        }

        $this->info("🚀 Starting batch forecast with {$model} model...");
        $this->newLine();

        // ── Step 1: Check Flask server ──
        $flaskAvailable = $this->checkFlaskServer();

        if (!$flaskAvailable) {
            $this->warn('⚠️  Flask server is not available. Falling back to Simple Moving Average.');
            $model = 'sma';
        }

        // ── Step 2: Train model (if Flask is available) ──
        if ($model !== 'sma' && !$skipTrain) {
            $this->info("📚 Step 1: Training {$model} model...");
            $trainResult = $this->trainModel($model);

            if ($trainResult === null) {
                $this->warn('⚠️  Training failed. Falling back to SMA.');
                $model = 'sma';
            } else {
                $this->info(
                    '✅ Model trained! MAE: ' . $this->formatMetric($trainResult['mae'] ?? null)
                    . ', RMSE: ' . $this->formatMetric($trainResult['rmse'] ?? null)
                    . ', WMAPE: ' . $this->formatMetric($trainResult['wmape'] ?? null)
                );
            }
            $this->newLine();
        } elseif ($skipTrain && $model !== 'sma') {
            $this->info("⏭️  Skipping training, using existing saved model.");
            $this->newLine();
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
        $failedProducts = [];
        $successMaeTotal = 0.0;
        $successMaeCount = 0;
        $successWmapeTotal = 0.0;
        $successWmapeCount = 0;

        $query->chunk(self::CHUNK_SIZE, function ($products) use (
            $model,
            &$successCount,
            &$failCount,
            &$skippedCount,
            &$failedProducts,
            &$successMaeTotal,
            &$successMaeCount,
            &$successWmapeTotal,
            &$successWmapeCount,
            $progressBar
        ) {
            foreach ($products as $product) {
                try {
                    $salesData = $this->getSalesHistory($product->IdRoster);

                    if ($salesData['bulan']->isEmpty()) {
                        $product->update([
                            'forecasted_demand' => 0,
                            'forecast_model' => 'none',
                            'forecast_status' => 'safe',
                            'last_forecast_at' => now()
                        ]);
                        $skippedCount++;
                        $progressBar->advance();
                        continue;
                    }

                    if ($model === 'sma') {
                        $forecast = $this->calculateSMA($salesData);
                        $forecastModel = 'sma';
                        $maeScore = null;
                        $wmapeScore = null;
                    } else {
                        $predictionResult = $this->callFlaskPredict($model, $salesData, $product->IdRoster);
                        $forecastModel = $model;

                        if ($predictionResult === null) {
                            $failCount++;
                            $failedProducts[] = $product->IdRoster;
                            $progressBar->advance();
                            continue;
                        }

                        $forecast = $predictionResult['forecast'];
                        $maeScore = $predictionResult['mae'];
                        $wmapeScore = $predictionResult['wmape'];

                        if ($maeScore !== null) {
                            $successMaeTotal += (float) $maeScore;
                            $successMaeCount++;
                        }

                        if ($wmapeScore !== null) {
                            $successWmapeTotal += (float) $wmapeScore;
                            $successWmapeCount++;
                        }
                    }

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
                        'mae_score' => $maeScore,
                        'wmape_score' => $wmapeScore,
                    ]);

                    $successCount++;
                } catch (\Exception $e) {
                    Log::error("Forecast failed for product {$product->IdRoster}", [
                        'error' => $e->getMessage()
                    ]);
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
                ['Duration', "{$duration}s"],
                ['Model Used', strtoupper($model)]
            ]
        );

        $this->line('SUMMARY: ' . json_encode([
            'success' => $successCount,
            'failed' => $failCount,
            'skipped' => $skippedCount,
            'total' => $totalProducts,
            'failed_products' => $failedProducts,
            'model' => strtoupper($model),
            'metrics' => [
                'mae' => $successMaeCount > 0 ? round($successMaeTotal / $successMaeCount, 4) : null,
                'wmape' => $successWmapeCount > 0 ? round($successWmapeTotal / $successWmapeCount, 4) : null,
            ],
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
     * Train the model by sending aggregated sales data
     */
    private function trainModel(string $model): ?array
    {
        try {
            // Gather aggregated monthly sales across all products
            $salesData = DB::table('detail_transaksi')
                ->join('transaksi', 'detail_transaksi.IdTransaksi', '=', 'transaksi.IdTransaksi')
                ->select(
                    DB::raw($this->monthExpression() . ' as bulan'),
                    DB::raw('SUM(detail_transaksi.QtyProduk) as terjual')
                )
                ->where('transaksi.tglTransaksi', '>=', Carbon::now()->subMonths(24))
                ->groupBy('bulan')
                ->orderBy('bulan')
                ->get();

            $originalCount = $salesData->count();
            $salesData = $this->buildMinimumTrainingDataset($salesData, 6);

            if ($salesData->count() < 6) {
                $this->warn("   ⚠️  Only {$salesData->count()} months of data. Minimum 6 required for training.");
                return null;
            }

            if ($originalCount < 6) {
                $this->warn("   ⚠️  Only {$originalCount} months of real data. Padded with dummy months (0 sales) to reach 6 months for training.");
            }

            $endpoint = $model === 'lstm' ? '/train/lstm' : '/train/prophet';

            $payload = [
                'bulan' => $salesData->pluck('bulan')->toArray(),
                'terjual' => $salesData->pluck('terjual')->toArray()
            ];

            Log::debug('Batch training payload', [
                'model' => $model,
                'payload' => [
                    'bulan_count' => count($payload['bulan']),
                    'terjual_count' => count($payload['terjual']),
                ],
            ]);

            $response = Http::timeout(self::TIMEOUT_SECONDS)
                ->asJson()
                ->post(self::FLASK_BASE_URL . $endpoint, $payload);

            if ($response->successful()) {
                $payload = $response->json();
                $metrics = is_array($payload['metrics'] ?? null) ? $payload['metrics'] : [];

                return [
                    'mae' => isset($metrics['mae']) ? (float) $metrics['mae'] : null,
                    'rmse' => isset($metrics['rmse']) ? (float) $metrics['rmse'] : null,
                    'wmape' => isset($metrics['wmape']) ? (float) $metrics['wmape'] : null,
                ];
            }

            $this->warn("   ⚠️  Training returned error: " . ($response->json()['error'] ?? 'Unknown'));
            return null;
        } catch (\Exception $e) {
            Log::warning("Training failed: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Build minimum contiguous monthly dataset in-memory without writing dummy rows to DB.
     */
    private function buildMinimumTrainingDataset($salesData, int $minimumMonths = 6)
    {
        if ($salesData->count() >= $minimumMonths) {
            return $salesData;
        }

        $existing = [];
        foreach ($salesData as $row) {
            $existing[$row->bulan] = (float) $row->terjual;
        }

        $lastMonth = empty($existing)
            ? Carbon::now()->startOfMonth()
            : Carbon::createFromFormat('Y-m', max(array_keys($existing)))->startOfMonth();

        $finalData = collect();
        for ($i = $minimumMonths - 1; $i >= 0; $i--) {
            $bulan = $lastMonth->copy()->subMonths($i)->format('Y-m');
            $finalData->push((object) [
                'bulan' => $bulan,
                'terjual' => (float) ($existing[$bulan] ?? 0),
            ]);
        }

        return $finalData;
    }

    /**
     * Get sales history for a single product (last 12 months)
     */
    private function getSalesHistory(string $idRoster): array
    {
        $salesData = DB::table('detail_transaksi')
            ->join('transaksi', 'detail_transaksi.IdTransaksi', '=', 'transaksi.IdTransaksi')
            ->select(
                DB::raw($this->monthExpression() . ' as bulan'),
                DB::raw('SUM(detail_transaksi.QtyProduk) as terjual')
            )
            ->where('detail_transaksi.IdRoster', $idRoster)
            ->where('transaksi.tglTransaksi', '>=', Carbon::now()->subMonths(12))
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

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
    private function callFlaskPredict(string $model, array $salesData, string $idRoster): ?array
    {
        try {
            $endpoint = $model === 'lstm' ? '/predictlstm' : '/predictprophet';
            $dataType = $this->resolveDataTypeByProduct($idRoster);
            $modelName = $this->mapModelName($model, $dataType);

            $payload = [
                'bulan' => $salesData['bulan']->toArray(),
                'terjual' => $salesData['terjual']->toArray(),
                'data_type' => $dataType,
                'model_name' => $modelName,
            ];

            Log::debug('Batch forecast payload', [
                'id_roster' => $idRoster,
                'endpoint' => $endpoint,
                'payload' => [
                    'bulan_count' => count($payload['bulan']),
                    'terjual_count' => count($payload['terjual']),
                    'data_type' => $payload['data_type'],
                    'model_name' => $payload['model_name'],
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
                $metrics = is_array($result['metrics'] ?? null) ? $result['metrics'] : [];

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
                    'data_type' => $dataType,
                    'model_name' => $result['model']['model_name'] ?? $modelName,
                ]);

                return [
                    'forecast' => (float) $forecastValue,
                    'mae' => isset($metrics['mae']) ? (float) $metrics['mae'] : null,
                    'wmape' => array_key_exists('wmape', $metrics) && $metrics['wmape'] !== null
                        ? (float) $metrics['wmape']
                        : null,
                ];
            }

            Log::warning('Flask predict returned non-success status', [
                'id_roster' => $idRoster,
                'endpoint' => $endpoint,
                'framework' => $model,
                'data_type' => $dataType,
                'model_name' => $modelName,
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
     * Product-level type selection from transaction behavior.
     */
    private function resolveDataTypeByProduct(string $idRoster): string
    {
        if (Schema::hasColumn('detail_transaksi', 'data_type')) {
            $hasBoronganLine = DB::table('detail_transaksi')
                ->where('IdRoster', $idRoster)
                ->where('data_type', 'Borongan')
                ->exists();
        } else {
            $hasBoronganLine = DB::table('detail_transaksi')
                ->where('IdRoster', $idRoster)
                ->where('QtyProduk', '>', 100)
                ->exists();
        }

        return $hasBoronganLine ? 'Borongan' : 'Eceran';
    }

    /**
     * Explicit framework+data_type to model pinning.
     */
    private function mapModelName(string $framework, string $dataType): string
    {
        $framework = strtolower($framework);
        $normalizedType = strtolower($dataType) === 'borongan' ? 'borongan' : 'eceran';

        $map = [
            'lstm' => [
                'eceran' => 'lstm_eceran_tuned',
                'borongan' => 'lstm_borongan',
            ],
            'prophet' => [
                'eceran' => 'prophet_eceran_tuned',
                'borongan' => 'prophet_borongan',
            ],
        ];

        return $map[$framework][$normalizedType] ?? ($framework . '_' . $normalizedType);
    }

    private function formatMetric(?float $value): string
    {
        if ($value === null) {
            return 'N/A';
        }

        return rtrim(rtrim(number_format($value, 4, '.', ''), '0'), '.');
    }

    /**
     * Calculate Simple Moving Average (fallback)
     */
    private function calculateSMA(array $salesData): float
    {
        $values = $salesData['terjual'];

        if ($values->isEmpty()) {
            return 0;
        }

        $lastThree = $values->slice(-3);
        return $lastThree->avg();
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
