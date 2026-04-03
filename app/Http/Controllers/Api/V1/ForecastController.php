<?php
namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

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
            Log::info('Fetching sales data for forecasting', ['id_roster' => $idRoster]);

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

            $salesData = $query->where('transaksi.tglTransaksi', '>=', Carbon::now()->subMonths(12))
                ->groupBy('bulan')
                ->orderBy('bulan')
                ->get();

            Log::info('Raw sales data:', ['data' => $salesData->toArray()]);

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
                'data' => $salesData
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
                'model' => 'required|in:lstm,prophet'
            ]);

            $model = strtolower($request->input('model'));
            $idRoster = $request->input('id_roster');

            $bulan = $request->input('bulan');
            $terjual = $request->input('terjual');

            if (count($bulan) !== count($terjual)) {
                return back()->with('error', 'Jumlah data bulan dan terjual harus sama.');
            }

            $dataType = $this->resolveDataTypeByProduct($idRoster);
            $modelName = $this->mapModelName($model, $dataType);
            $endpoint = $model === 'lstm' ? '/predictlstm' : '/predictprophet';

            $payload = [
                'bulan' => $request->input('bulan'),
                'terjual' => $request->input('terjual'),
                'data_type' => $dataType,
                'model_name' => $modelName,
            ];

            Log::info('Manual forecast request', [
                'id_roster' => $idRoster,
                'framework' => $model,
                'data_type' => $dataType,
                'model_name' => $modelName,
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
                    'data_type' => $dataType,
                    'model_name' => $modelName,
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
            $result['data_type'] = $modelMeta['data_type'] ?? $dataType;
            $result['model_name'] = $modelMeta['model_name'] ?? $modelName;
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

    /**
     * Determine data_type from product transaction behavior.
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
     * Explicit model pinning map agreed in plan decisions.
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

    public function stockForecast()
    {
        try {
            // Get all products with cached forecast data
            $products = \App\Models\Produk::select(
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
                // If no forecast has been run, show placeholder
                if (!$product->last_forecast_at) {
                    $forecastData[] = [
                        'id_roster' => $product->IdRoster,
                        'nama_produk' => $product->NamaProduk,
                        'current_stock' => $product->stock ?? 0,
                        'forecasted_demand' => 0,
                        'forecast_model' => 'none',
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
                    'status' => $product->forecast_status ?? 'safe',
                    'last_forecast_at' => $product->last_forecast_at ?
                        Carbon::parse($product->last_forecast_at)->diffForHumans() :
                        'Never',
                    'safety_stock' => $product->safety_stock ?? 70
                ];
            }

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
                'lastUpdate' => $oldestForecast ? Carbon::parse($oldestForecast)->diffForHumans() : 'Never'
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
     * Run batch forecast via Artisan command (triggered from UI)
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
                'wmape' => null,
            ];

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
                'message' => 'Flask AI server is not reachable. SMA fallback will be used.'
            ]);
        }
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
