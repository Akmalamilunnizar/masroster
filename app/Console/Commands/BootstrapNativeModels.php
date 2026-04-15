<?php

namespace App\Console\Commands;

use App\Models\ModelHistory;
use App\Models\Produk;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BootstrapNativeModels extends Command
{
    protected $signature = 'app:bootstrap-native-models
        {--dry-run : Preview changes without writing to the database}
        {--force-replace : Replace existing active rows with the selected bootstrap version}';

    protected $description = 'Bootstrap legacy/native Flask model versions into model_histories as first active rows';

    private const FLASK_BASE_URL = 'http://127.0.0.1:5000';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $forceReplace = (bool) $this->option('force-replace');

        $this->info('Starting native model bootstrap...');

        $health = $this->fetchFlaskHealth();
        if ($health === null) {
            $this->error('Flask AI server is not reachable or returned an invalid health payload.');
            return Command::FAILURE;
        }

        $selectedModels = $this->selectBootstrapModels($health['available_models'] ?? []);
        if (empty($selectedModels)) {
            $this->error('No bootstrap-capable legacy models were found in Flask health data.');
            return Command::FAILURE;
        }

        $products = Produk::query()->select('IdRoster')->orderBy('IdRoster')->get();
        if ($products->isEmpty()) {
            $this->error('No products found in database.');
            return Command::FAILURE;
        }

        $summary = [
            'status' => 'success',
            'dry_run' => $dryRun,
            'force_replace' => $forceReplace,
            'selected_models' => $selectedModels,
            'inserted' => 0,
            'updated' => 0,
            'skipped_existing_active' => 0,
            'skipped_duplicate_version' => 0,
            'failed' => 0,
            'failed_products' => [],
        ];

        foreach ($products as $product) {
            foreach ($selectedModels as $framework => $model) {
                try {
                    $result = $this->bootstrapProductModel(
                        $product->IdRoster,
                        $framework,
                        $model,
                        $dryRun,
                        $forceReplace
                    );

                    $summary['inserted'] += $result['inserted'];
                    $summary['updated'] += $result['updated'];
                    $summary['skipped_existing_active'] += $result['skipped_existing_active'];
                    $summary['skipped_duplicate_version'] += $result['skipped_duplicate_version'];
                    $summary['failed'] += $result['failed'];

                    if (!empty($result['failed_products'])) {
                        $summary['failed_products'] = array_values(array_unique(array_merge(
                            $summary['failed_products'],
                            $result['failed_products']
                        )));
                    }
                } catch (\Throwable $e) {
                    Log::error('Bootstrap native model failed for roster', [
                        'id_roster' => $product->IdRoster,
                        'framework' => $framework,
                        'version_id' => $model['model_version'] ?? null,
                        'error' => $e->getMessage(),
                    ]);
                    $summary['failed']++;
                    $summary['failed_products'][] = $product->IdRoster;
                }
            }
        }

        $summary['failed_products'] = array_values(array_unique($summary['failed_products']));

        $this->info($dryRun ? 'Dry run complete.' : 'Bootstrap complete.');
        $this->line('SUMMARY: ' . json_encode($summary, JSON_UNESCAPED_UNICODE));

        return Command::SUCCESS;
    }

    private function fetchFlaskHealth(): ?array
    {
        try {
            $response = Http::timeout(30)->get(self::FLASK_BASE_URL . '/health');

            if (!$response->successful()) {
                Log::warning('Flask health check failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $payload = $response->json();
            if (!is_array($payload)) {
                return null;
            }

            return $payload;
        } catch (\Throwable $e) {
            Log::warning('Unable to fetch Flask health', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function selectBootstrapModels(array $availableModels): array
    {
        $selected = [];

        foreach (['LSTM', 'PROPHET'] as $framework) {
            $candidates = collect($availableModels)
                ->filter(function ($row) use ($framework) {
                    return strtoupper((string) ($row['framework'] ?? '')) === $framework
                        && !empty($row['model_version'])
                        && !empty($row['artifact_ready']);
                })
                ->sortBy(function ($row) {
                    $name = strtolower((string) ($row['model_name'] ?? ''));
                    $priority = 2;

                    if (str_contains($name, 'original')) {
                        $priority = 0;
                    } elseif (str_contains($name, 'tuned')) {
                        $priority = 1;
                    }

                    return sprintf('%d|%s|%s', $priority, (string) ($row['model_version'] ?? ''), $name);
                })
                ->values();

            if ($candidates->isEmpty()) {
                continue;
            }

            $chosen = $candidates->first();
            $selected[$framework] = [
                'model_name' => $chosen['model_name'] ?? null,
                'model_version' => $chosen['model_version'] ?? null,
                'framework' => $framework,
                'data_type' => $chosen['data_type'] ?? null,
                'bootstrap_source' => $chosen['bootstrap_source'] ?? 'legacy',
            ];
        }

        return $selected;
    }

    private function bootstrapProductModel(string $idRoster, string $framework, array $model, bool $dryRun, bool $forceReplace): array
    {
        $modelType = strtolower($framework);
        $versionId = (string) ($model['model_version'] ?? '');

        if ($versionId === '') {
            return [
                'inserted' => 0,
                'updated' => 0,
                'skipped_existing_active' => 1,
                'skipped_duplicate_version' => 0,
                'failed' => 1,
                'failed_products' => [$idRoster],
            ];
        }

        $existingActive = ModelHistory::query()
            ->where('id_roster', $idRoster)
            ->where('model_type', $modelType)
            ->where('is_active', true)
            ->first();

        $existingVersion = ModelHistory::query()
            ->where('id_roster', $idRoster)
            ->where('model_type', $modelType)
            ->where('version_id', $versionId)
            ->first();

        if ($existingActive && $existingActive->version_id === $versionId) {
            return [
                'inserted' => 0,
                'updated' => 0,
                'skipped_existing_active' => 1,
                'skipped_duplicate_version' => 0,
                'failed' => 0,
                'failed_products' => [],
            ];
        }

        if ($existingActive && !$forceReplace) {
            return [
                'inserted' => 0,
                'updated' => 0,
                'skipped_existing_active' => 1,
                'skipped_duplicate_version' => 0,
                'failed' => 0,
                'failed_products' => [],
            ];
        }

        if ($dryRun) {
            return [
                'inserted' => $existingVersion ? 0 : 1,
                'updated' => $existingVersion ? 1 : 0,
                'skipped_existing_active' => 0,
                'skipped_duplicate_version' => $existingVersion && $existingVersion->is_active ? 1 : 0,
                'failed' => 0,
                'failed_products' => [],
            ];
        }

        DB::transaction(function () use ($idRoster, $modelType, $model, $versionId, $existingVersion, $forceReplace) {
            if ($forceReplace || $existingVersion === null) {
                ModelHistory::query()
                    ->where('id_roster', $idRoster)
                    ->where('model_type', $modelType)
                    ->update(['is_active' => false]);
            }

            if ($existingVersion) {
                $existingVersion->update([
                    'wmape_score' => $existingVersion->wmape_score ?? ($model['wmape_score'] ?? null),
                    'mae_score' => $existingVersion->mae_score ?? ($model['mae_score'] ?? null),
                    'rmse_score' => $existingVersion->rmse_score ?? ($model['rmse_score'] ?? null),
                    'is_active' => true,
                ]);
                return;
            }

            ModelHistory::create([
                'id_roster' => $idRoster,
                'model_type' => $modelType,
                'version_id' => $versionId,
                'wmape_score' => $model['wmape_score'] ?? null,
                'mae_score' => $model['mae_score'] ?? null,
                'rmse_score' => $model['rmse_score'] ?? null,
                'is_active' => true,
            ]);
        });

        return [
            'inserted' => $existingVersion ? 0 : 1,
            'updated' => $existingVersion ? 1 : 0,
            'skipped_existing_active' => 0,
            'skipped_duplicate_version' => 0,
            'failed' => 0,
            'failed_products' => [],
        ];
    }
}
