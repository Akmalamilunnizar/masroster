<?php

namespace App\Console\Commands;

use App\Models\Produk;
use App\Models\ModelHistory;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TrainForecastModel extends Command
{
    protected $signature = 'app:train-model
        {--model=prophet : AI model to train (lstm or prophet)}';

    protected $description = 'Train global AI model only, then promote if WMAPE improves';

    private const FLASK_BASE_URL = 'http://127.0.0.1:5000';
    private const TIMEOUT_SECONDS = 180;

    public function handle(): int
    {
        $model = strtolower((string) $this->option('model'));

        if (!in_array($model, ['lstm', 'prophet'], true)) {
            $this->error("Invalid model. Use 'lstm' or 'prophet'.");
            return Command::FAILURE;
        }

        $this->info('🚀 Starting training-only flow for ' . strtoupper($model));

        if (!$this->checkFlaskServer()) {
            $this->error('Flask server is not reachable.');
            return Command::FAILURE;
        }

        $trainPayload = $this->buildTrainingPayload();
        if ($trainPayload === null) {
            $this->error('Insufficient data to train model. Need at least 6 months.');
            return Command::FAILURE;
        }

        $trainResult = $this->callTrainEndpoint($model, $trainPayload);
        if ($trainResult === null) {
            $this->error('Training endpoint returned failure.');
            return Command::FAILURE;
        }

        $metrics = $trainResult['metrics'];
        $modelVersion = $trainResult['model_version'];

        $wmape = $metrics['wmape'];
        $mae = $metrics['mae'];
        $rmse = $metrics['rmse'];

        $insertedCount = 0;
        $deactivatedCount = 0;
        $failedProducts = [];

        Produk::query()->select('IdRoster')->orderBy('IdRoster')->chunk(100, function ($products) use (
            $model,
            $modelVersion,
            $wmape,
            $mae,
            $rmse,
            &$insertedCount,
            &$deactivatedCount,
            &$failedProducts
        ) {
            foreach ($products as $product) {
                try {
                    DB::transaction(function () use ($product, $model, $modelVersion, $wmape, $mae, $rmse, &$deactivatedCount) {
                        $deactivatedCount += ModelHistory::query()
                            ->where('id_roster', $product->IdRoster)
                            ->where('model_type', $model)
                            ->update(['is_active' => false]);

                        ModelHistory::create([
                            'id_roster' => $product->IdRoster,
                            'model_type' => $model,
                            'version_id' => $modelVersion,
                            'wmape_score' => $wmape,
                            'mae_score' => $mae,
                            'rmse_score' => $rmse,
                            'is_active' => true,
                        ]);
                    });

                    $insertedCount++;
                } catch (\Throwable $e) {
                    Log::error('Failed to persist model history', [
                        'id_roster' => $product->IdRoster,
                        'model' => $model,
                        'version_id' => $modelVersion,
                        'error' => $e->getMessage(),
                    ]);
                    $failedProducts[] = $product->IdRoster;
                }
            }
        });

        $summary = [
            'status' => 'success',
            'model' => strtoupper($model),
            'model_version' => $modelVersion,
            'metrics' => $metrics,
            'inserted' => $insertedCount,
            'deactivated' => $deactivatedCount,
            'promoted' => $insertedCount,
            'rejected' => count($failedProducts),
            'failed_products' => $failedProducts,
        ];

        $this->info('✅ Training flow finished.');
        $this->line('SUMMARY: ' . json_encode($summary, JSON_UNESCAPED_UNICODE));

        return Command::SUCCESS;
    }

    private function checkFlaskServer(): bool
    {
        try {
            $response = Http::timeout(5)->get(self::FLASK_BASE_URL . '/health');
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    private function buildTrainingPayload(): ?array
    {
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

        if ($salesData->count() < 6) {
            return null;
        }

        return [
            'bulan' => $salesData->pluck('bulan')->toArray(),
            'terjual' => $salesData->pluck('terjual')->toArray(),
        ];
    }

    private function callTrainEndpoint(string $model, array $payload): ?array
    {
        try {
            $endpoint = $model === 'lstm' ? '/train/lstm' : '/train/prophet';

            $response = Http::timeout(self::TIMEOUT_SECONDS)
                ->asJson()
                ->post(self::FLASK_BASE_URL . $endpoint, $payload);

            if (!$response->successful()) {
                Log::warning('Train endpoint failed', [
                    'model' => $model,
                    'status' => $response->status(),
                    'error' => $response->json('error') ?? $response->body(),
                ]);
                return null;
            }

            $result = $response->json();
            $metrics = is_array($result['metrics'] ?? null) ? $result['metrics'] : [];
            $modelVersion = (string) ($result['model_version'] ?? '');

            if ($modelVersion === '') {
                Log::warning('Train endpoint response missing model_version', ['payload' => $result]);
                return null;
            }

            return [
                'model_version' => $modelVersion,
                'metrics' => [
                    'mae' => isset($metrics['mae']) ? (float) $metrics['mae'] : null,
                    'rmse' => isset($metrics['rmse']) ? (float) $metrics['rmse'] : null,
                    'wmape' => array_key_exists('wmape', $metrics) && $metrics['wmape'] !== null
                        ? (float) $metrics['wmape']
                        : null,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Train endpoint exception', [
                'model' => $model,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function monthExpression(): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', transaksi.tglTransaksi)"
            : "DATE_FORMAT(transaksi.tglTransaksi, '%Y-%m')";
    }
}
