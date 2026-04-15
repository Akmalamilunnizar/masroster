<?php
namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Produk;
use App\Models\ModelHistory;

class ForecastController extends Controller
{
    public function showForm()
    {
        $products = \App\Models\Produk::select('IdRoster', 'NamaProduk')->orderBy('NamaProduk')->get();
        return view('admin.forecast.form', compact('products'));
    }

    public function getSalesData(Request $request)
    {
        try {
            $idRoster = $request->query('id_roster');
            $versionId = $request->query('version_id');
            $modelType = $request->query('model');

            $resolvedDataType = $this->resolveForecastDataType(
                $idRoster ? (int) $idRoster : null,
                $versionId ? (int) $versionId : null,
                $modelType ? strtolower((string) $modelType) : null
            );

            Log::info('Fetching sales data for forecasting', [
                'id_roster' => $idRoster,
                'version_id' => $versionId,
                'model_type' => $modelType,
                'data_type' => $resolvedDataType,
            ]);

            // Get the last 12 months of sales data
            $query = DB::table('detail_transaksi')
                ->join('transaksi', 'detail_transaksi.IdTransaksi', '=', 'transaksi.IdTransaksi')
                ->select(
                    DB::raw($this->monthExpression() . ' as bulan'),
                    DB::raw('SUM(detail_transaksi.QtyProduk) as terjual')
                );

            if ($idRoster) {
                // From DetailTransaksi model: 'IdRoster'
                $query->where('detail_transaksi.IdRoster', $idRoster);
            }

            $this->applyDataTypeFilter($query, $resolvedDataType);

            $salesData = $query->where('transaksi.tglTransaksi', '>=', Carbon::now()->subMonths(12))
                ->groupBy('bulan')
                ->orderBy('bulan')
                ->get();

            Log::info('Raw sales data:', [
                'data_type' => $resolvedDataType,
                'count' => $salesData->count(),
                'data' => $salesData->toArray(),
            ]);

            if (in_array($resolvedDataType, ['Eceran', 'Borongan'], true) && $salesData->count() < 12) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Data {$resolvedDataType} untuk roster ini belum cukup. Minimal 12 titik data dibutuhkan agar forecast tidak tercampur."
                ], 422);
            }

            // If we don't have enough data, fill with 0s to reach 12 points
            if ($salesData->count() < 12) {
                Log::info('Not enough data, generating placeholder data to reach 12 points');

                $existingBulans = $salesData->pluck('terjual', 'bulan')->toArray();
                $finalData = collect();
                $currentDate = Carbon::now();

                for ($i = 0; $i < 12; $i++) {
                    $date = $currentDate->copy()->subMonths($i)->format('Y-m');
                    $finalData->push([
                        'bulan' => $date,
                        'terjual' => (int)($existingBulans[$date] ?? 0)
                    ]);
                }

                $salesData = $finalData->sortBy('bulan')->values();
            }

            Log::info('Final sales data:', ['data' => $salesData->toArray()]);

            return response()->json([
                'status' => 'success',
                'data' => $salesData,
                'data_type' => $resolvedDataType,
                'histories' => $idRoster
                    ? ModelHistory::query()
                        ->where('id_roster', $idRoster)
                        ->orderByDesc('created_at')
                        ->get(['id', 'id_roster', 'model_type', 'version_id', 'data_type', 'wmape_score', 'mae_score', 'rmse_score', 'is_active', 'created_at'])
                        ->map(function ($history) {
                            return [
                                'id' => $history->id,
                                'id_roster' => $history->id_roster,
                                'model_type' => $history->model_type,
                                'version_id' => $history->version_id,
                                'data_type' => $history->data_type,
                                'wmape_score' => $history->wmape_score,
                                'mae_score' => $history->mae_score,
                                'rmse_score' => $history->rmse_score,
                                'is_active' => $history->is_active,
                                'label' => sprintf(
                                    '%s - %s (WMAPE: %s)%s',
                                    strtoupper($history->model_type),
                                    $history->version_id,
                                    $history->wmape_score === null ? 'N/A' : rtrim(rtrim(number_format((float) $history->wmape_score, 2, '.', ''), '0'), '.'),
                                    $history->is_active ? ' [AKTIF]' : ' [LAMA]'
                                ),
                            ];
                        })
                        ->values()
                        ->all()
                    : []
            ]);

        } catch (\Exception $e) {
            Log::error('Error in getSalesData: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error fetching sales data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function predict(Request $request)
    {
        try {
            $request->validate([
                'id_roster' => 'required|exists:produk,IdRoster',
                'bulan' => 'required|array|min:12',
                'terjual' => 'required|array|min:12',
                'bulan.*' => 'required|date_format:Y-m',
                'terjual.*' => 'required|numeric',
                'model' => 'required|in:lstm,prophet',
                'version_id' => 'required|exists:model_histories,id'
            ]);

            $idRoster = $request->input('id_roster');
            $history = ModelHistory::query()
                ->where('id', $request->input('version_id'))
                ->where('id_roster', $idRoster)
                ->firstOrFail();

            $model = strtolower((string) $history->model_type);
            $dataType = ucfirst(strtolower(trim((string) ($history->data_type ?? ''))));

            if ($request->input('model') !== $model) {
                return back()->with('error', 'Model dan versi yang dipilih tidak sesuai.');
            }

            $bulan = $request->input('bulan');
            $terjual = $request->input('terjual');

            if (count($bulan) !== count($terjual)) {
                return back()->with('error', 'Jumlah data bulan dan terjual harus sama.');
            }

            $endpoint = $model === 'lstm' ? '/predictlstm' : '/predictprophet';

            $payload = [
                'bulan' => $request->input('bulan'),
                'terjual' => $request->input('terjual'),
                'model_version' => $history->version_id,
            ];

            Log::info('Manual forecast request', [
                'id_roster' => $idRoster,
                'framework' => $model,
                'model_version' => $history->version_id,
                'data_type' => $dataType,
            ]);

            $response = Http::timeout(30)
                ->connectTimeout(30)
                ->asJson()
                ->post('http://127.0.0.1:5000' . $endpoint, $payload);

            if (!$response->successful()) {
                $errorMessage = $response->json('error') ?? $response->body() ?? 'Unknown error';

                Log::warning('Manual forecast failed', [
                    'id_roster' => $idRoster,
                    'endpoint' => $endpoint,
                    'framework' => $model,
                    'model_version' => $history->version_id,
                    'status' => $response->status(),
                    'error' => $errorMessage,
                ]);

                return back()->with('error', 'Layanan forecasting mengembalikan error: ' . $errorMessage);
            }

            $result = $response->json();

            if (!is_array($result) || !isset($result['forecast']) || !is_array($result['forecast'])) {
                throw new \Exception('Invalid response from forecasting service');
            }

            $metrics = is_array($result['metrics'] ?? null) ? $result['metrics'] : [];
            $modelMeta = is_array($result['model'] ?? null) ? $result['model'] : [];

            // Add presentation fields expected by result blade.
            $result['model'] = strtoupper((string) ($modelMeta['framework'] ?? $model));
            $result['data_type'] = $modelMeta['data_type'] ?? 'Global';
            $result['model_name'] = $modelMeta['model_name'] ?? null;
            $result['model_version'] = $modelMeta['model_version'] ?? $history->version_id;
            $result['mae'] = $metrics['mae'] ?? 0;
            $result['rmse'] = $metrics['rmse'] ?? 0;
            $result['wmape'] = $metrics['wmape'] ?? null;

            return view('admin.forecast.result', ['result' => (object) $result]);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return back()->with('error', 'Tidak dapat terhubung ke layanan forecasting. Pastikan server Flask berjalan di http://127.0.0.1:5000');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function stockForecast()
    {
        try {
            // Get all products with cached forecast data
                $products = \App\Models\Produk::with(['activeLstmHistory', 'activeProphetHistory', 'modelHistories'])->select(
                    'IdRoster',
                    'NamaProduk',
                    'stock',
                    'forecasted_demand',
                    'forecast_model',
                    'forecast_status',
                    'last_forecast_at',
                    'safety_stock'
                )
                ->orderBy('forecast_status', 'asc') // Critical first
                ->orderBy('NamaProduk')
                ->get();

            $forecastData = [];
            $currentMonth = Carbon::now();

            foreach ($products as $product) {
                $activeHistories = collect([
                    $product->activeLstmHistory,
                    $product->activeProphetHistory,
                ])->filter();

                $activeLstmVersion = optional($product->activeLstmHistory)->version_id;
                $activeProphetVersion = optional($product->activeProphetHistory)->version_id;

                // If no forecast has been run, show placeholder
                if (!$product->last_forecast_at) {
                    $forecastData[] = [
                        'id_roster' => $product->IdRoster,
                        'nama_produk' => $product->NamaProduk,
                        'current_stock' => $product->stock ?? 0,
                        'forecasted_demand' => 0,
                        'forecast_model' => 'none',
                        'active_lstm_version' => $activeLstmVersion,
                        'active_prophet_version' => $activeProphetVersion,
                        'status' => 'safe',
                        'last_forecast_at' => null,
                        'safety_stock' => $product->safety_stock ?? 70
                    ];
                    continue;
                }

                $forecastData[] = [
                    'id_roster' => $product->IdRoster,
                    'nama_produk' => $product->NamaProduk,
                    'current_stock' => $product->stock ?? 0,
                    'forecasted_demand' => $product->forecasted_demand ?? 0,
                    'forecast_model' => strtoupper($product->forecast_model ?? 'N/A'),
                    'active_lstm_version' => $activeLstmVersion,
                    'active_prophet_version' => $activeProphetVersion,
                    'status' => $product->forecast_status ?? 'safe',
                    'last_forecast_at' => $product->last_forecast_at ?
                        Carbon::parse($product->last_forecast_at)->diffForHumans() :
                        'Never',
                    'safety_stock' => $product->safety_stock ?? 70
                ];
            }

            $healthRows = $products->flatMap(function ($product) {
                return $product->modelHistories->sortByDesc('created_at')->values()->map(function ($history) use ($product) {
                    return [
                        'id' => $history->id,
                        'nama_produk' => $product->NamaProduk,
                        'algoritma_aktif' => strtoupper($history->model_type),
                        'version_id' => $history->version_id,
                        'wmape_score' => $history->wmape_score,
                        'is_active' => (bool) $history->is_active,
                    ];
                });
            })->values();

            // Check if forecasts are stale (older than 30 days)
            $hasForecasts = $products->whereNotNull('last_forecast_at')->count() > 0;
            $oldestForecast = $products->whereNotNull('last_forecast_at')
                ->min('last_forecast_at');

            $needsUpdate = false;
            if ($oldestForecast && Carbon::parse($oldestForecast)->lt(now()->subDays(30))) {
                $needsUpdate = true;
            }

            return view('admin.forecast.stock', [
                'forecastData' => $forecastData,
                'month' => $currentMonth->format('F Y'),
                'hasForecasts' => $hasForecasts,
                'needsUpdate' => $needsUpdate,
                'lastUpdate' => $oldestForecast ? Carbon::parse($oldestForecast)->diffForHumans() : 'Never',
                'healthRows' => $healthRows,
            ]);

        } catch (\Exception $e) {
            Log::error('Error in stockForecast: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Terjadi kesalahan saat menghitung forecast stok: ' . $e->getMessage());
        }
    }

    /**
    * Run fast batch forecast via inference-only command.
     */
    public function runBatchForecast(Request $request)
    {
        $request->validate([
            'model' => 'required|in:lstm,prophet',
            'force' => 'nullable|boolean'
        ]);

        $model = $request->input('model');
        $force = $request->boolean('force', false);

        try {
            $params = ['--model' => $model];
            if ($force) {
                $params['--force'] = true;
            }

            $startTime = microtime(true);

            $exitCode = Artisan::call('app:forecast-all', $params);
            $output = Artisan::output();
            $summary = $this->extractBatchSummary($output);
            $metrics = is_array($summary['metrics'] ?? null) ? $summary['metrics'] : [
                'mae' => null,
                'rmse' => null,
                'wmape' => null,
            ];
            $details = is_array($summary['details'] ?? null) ? $summary['details'] : [];
            if (!empty($details)) {
                $request->session()->flash('batch_results', $details);
            }

            $duration = round(microtime(true) - $startTime, 2);

            if ($exitCode === 0 && is_array($summary)) {
                $failed = (int) ($summary['failed'] ?? 0);
                $successful = (int) ($summary['success'] ?? 0);

                if ($failed > 0 && $successful > 0) {
                    return response()->json([
                        'status' => 'partial_success',
                        'message' => $this->formatSuccessMessage(
                            'Batch forecast selesai sebagian. Beberapa produk gagal diprediksi.',
                            $metrics
                        ),
                        'duration' => $duration . 's',
                        'summary' => $summary,
                        'metrics' => $metrics,
                        'details' => $details,
                        'output' => $output
                    ]);
                }

                if ($failed === 0 && $successful > 0) {
                    return response()->json([
                        'status' => 'success',
                        'message' => $this->formatSuccessMessage(
                            'Batch forecast berhasil dijalankan.',
                            $metrics
                        ),
                        'duration' => $duration . 's',
                        'summary' => $summary,
                        'metrics' => $metrics,
                        'details' => $details,
                        'output' => $output
                    ]);
                }

                return response()->json([
                    'status' => 'error',
                    'message' => 'Batch forecast gagal diproses.',
                    'duration' => $duration . 's',
                    'summary' => $summary,
                    'output' => $output
                ], 500);
            }

            if ($exitCode === 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => $this->formatSuccessMessage(
                        "Batch forecast berhasil dijalankan dengan model " . strtoupper($model) . ".",
                        $metrics
                    ),
                    'duration' => $duration . 's',
                    'metrics' => $metrics,
                    'output' => $output
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Batch forecast gagal.',
                    'output' => $output
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Batch forecast error: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Trigger train-only flow and promote best model version.
     */
    public function trainModel(Request $request)
    {
        $request->validate([
            'model' => 'required|in:lstm,prophet',
        ]);

        $model = $request->input('model');

        try {
            $startTime = microtime(true);
            $exitCode = Artisan::call('app:train-model', ['--model' => $model]);
            $output = Artisan::output();
            $summary = $this->extractBatchSummary($output);
            $duration = round(microtime(true) - $startTime, 2);

            if ($exitCode === 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Training model selesai.',
                    'duration' => $duration . 's',
                    'summary' => $summary,
                    'output' => $output,
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Training model gagal.',
                'duration' => $duration . 's',
                'output' => $output,
            ], 500);
        } catch (\Exception $e) {
            Log::error('Train model error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function setActiveModel(int $id_history)
    {
        try {
            $history = null;
            DB::transaction(function () use ($id_history, &$history) {
                $history = ModelHistory::query()->lockForUpdate()->findOrFail($id_history);

                ModelHistory::query()
                    ->where('id_roster', $history->id_roster)
                    ->where('model_type', $history->model_type)
                    ->update(['is_active' => false]);

                $history->update(['is_active' => true]);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Model berhasil dijadikan aktif.',
            ]);
        } catch (\Exception $e) {
            Log::error('Rollback/set-active error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengubah model aktif: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function globalOverride(Request $request)
    {
        $request->validate([
            'model_type' => 'required|in:lstm,prophet',
            'version_date_range' => 'nullable|array',
        ]);

        $modelType = strtolower($request->input('model_type'));
        $range = $request->input('version_date_range', []);
        $start = $range['start'] ?? $range['from'] ?? null;
        $end = $range['end'] ?? $range['to'] ?? null;

        $updated = 0;
        $skipped = 0;

        try {
            DB::transaction(function () use ($modelType, $start, $end, &$updated, &$skipped) {
                $rosters = Produk::query()->select('IdRoster')->get();

                foreach ($rosters as $product) {
                    $query = ModelHistory::query()
                        ->where('id_roster', $product->IdRoster)
                        ->where('model_type', $modelType);

                    if ($start) {
                        $query->where('created_at', '>=', $start);
                    }

                    if ($end) {
                        $query->where('created_at', '<=', $end);
                    }

                    $target = $query->orderByDesc('created_at')->first();

                    if (!$target) {
                        $skipped++;
                        continue;
                    }

                    ModelHistory::query()
                        ->where('id_roster', $product->IdRoster)
                        ->where('model_type', $modelType)
                        ->update(['is_active' => false]);

                    $target->update(['is_active' => true]);
                    $updated++;
                }
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Global override berhasil diterapkan.',
                'updated' => $updated,
                'skipped' => $skipped,
            ]);
        } catch (\Exception $e) {
            Log::error('Global override error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menjalankan global override: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function autoOptimize(Request $request)
    {
        $request->validate([
            'model_type' => 'nullable|in:lstm,prophet',
        ]);

        $types = $request->filled('model_type')
            ? [strtolower($request->input('model_type'))]
            : ['lstm', 'prophet'];

        $optimized = 0;
        $skipped = 0;

        try {
            DB::transaction(function () use ($types, &$optimized, &$skipped) {
                foreach (Produk::query()->select('IdRoster')->get() as $product) {
                    foreach ($types as $type) {
                        $histories = ModelHistory::query()
                            ->where('id_roster', $product->IdRoster)
                            ->where('model_type', $type)
                            ->get();

                        if ($histories->isEmpty()) {
                            $skipped++;
                            continue;
                        }

                        $best = $histories
                            ->sortBy(fn ($history) => $history->wmape_score === null ? PHP_FLOAT_MAX : (float) $history->wmape_score)
                            ->first();

                        ModelHistory::query()
                            ->where('id_roster', $product->IdRoster)
                            ->where('model_type', $type)
                            ->update(['is_active' => false]);

                        $best->update(['is_active' => true]);
                        $optimized++;
                    }
                }
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Auto-optimize berhasil dijalankan.',
                'optimized' => $optimized,
                'skipped' => $skipped,
            ]);
        } catch (\Exception $e) {
            Log::error('Auto optimize error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menjalankan auto-optimize: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check Flask AI server health status
     */
    public function checkFlaskHealth()
    {
        try {
            $response = Http::timeout(5)->get('http://127.0.0.1:5000/health');
            $payload = $response->json();

            if ($response->successful()) {
                return response()->json([
                    'status' => 'online',
                    'models' => $payload['models'] ?? null,
                    'registry_loaded' => $payload['registry_loaded'] ?? false,
                    'available_models' => $payload['available_models'] ?? [],
                    'message' => 'Flask AI server is running.'
                ]);
            }

            return response()->json([
                'status' => 'offline',
                'message' => 'Flask server returned an error.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'offline',
                'message' => 'Flask AI server is not reachable. Fast inference requires the AI server online.'
            ]);
        }
    }

    private function resolveForecastDataType(?int $idRoster = null, ?int $versionId = null, ?string $modelType = null): ?string
    {
        $query = ModelHistory::query();

        if ($versionId !== null) {
            $query->where('id', $versionId);
        }

        if ($idRoster !== null) {
            $query->where('id_roster', $idRoster);
        }

        if ($modelType !== null && $modelType !== '') {
            $query->whereRaw('LOWER(model_type) = ?', [strtolower($modelType)]);
        }

        $history = $query->first();

        if (!$history && $idRoster !== null && $modelType !== null && $modelType !== '') {
            $history = ModelHistory::query()
                ->where('id_roster', $idRoster)
                ->whereRaw('LOWER(model_type) = ?', [strtolower($modelType)])
                ->where('is_active', true)
                ->orderByDesc('created_at')
                ->first();
        }

        if (!$history && $idRoster !== null) {
            $history = ModelHistory::query()
                ->where('id_roster', $idRoster)
                ->where('is_active', true)
                ->orderByDesc('created_at')
                ->first();
        }

        $dataType = ucfirst(strtolower(trim((string) ($history?->data_type ?? ''))));

        return in_array($dataType, ['Eceran', 'Borongan'], true) ? $dataType : null;
    }

    private function applyDataTypeFilter($query, ?string $dataType)
    {
        if (in_array($dataType, ['Eceran', 'Borongan'], true)) {
            $query->where('detail_transaksi.data_type', $dataType);
        }

        return $query;
    }

    private function monthExpression(): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', transaksi.tglTransaksi)"
            : "DATE_FORMAT(transaksi.tglTransaksi, '%Y-%m')";
    }

    private function extractBatchSummary(string $output): ?array
    {
        if (!preg_match('/SUMMARY:\s*(\{.*\})/s', $output, $matches)) {
            return null;
        }

        $summary = json_decode($matches[1], true);
        return is_array($summary) ? $summary : null;
    }

    private function formatSuccessMessage(string $message, array $metrics): string
    {
        $parts = [$message];

        if (isset($metrics['mae']) && $metrics['mae'] !== null) {
            $parts[] = 'MAE: ' . rtrim(rtrim(number_format((float) $metrics['mae'], 4, '.', ''), '0'), '.');
        }

        if (isset($metrics['wmape']) && $metrics['wmape'] !== null) {
            $parts[] = 'WMAPE: ' . rtrim(rtrim(number_format((float) $metrics['wmape'], 4, '.', ''), '0'), '.');
        }

        return implode(' | ', $parts);
    }
}
