<?php
namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
                    DB::raw('DATE_FORMAT(transaksi.tglTransaksi, "%Y-%m") as bulan'),
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
                'bulan' => 'required|array|min:12',
                'terjual' => 'required|array|min:12',
                'bulan.*' => 'required|date_format:Y-m',
                'terjual.*' => 'required|numeric',
                'model' => 'required|in:lstm,prophet'
            ]);

            $client = new Client([
                'timeout' => 30,
                'connect_timeout' => 30
            ]);

            $data = [
                'bulan' => $request->input('bulan'),
                'terjual' => $request->input('terjual')
            ];

            // Dete 

            $body = $response->getBody();
            $result = json_decode($body);

            if (!$result) {
                throw new \Exception('Invalid response from forecasting service');
            }

            // Add model info to result for display
            $result->model = strtoupper($model);

            return view('admin.forecast.result', ['result' => $result]);

        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            return back()->with('error', 'Tidak dapat terhubung ke layanan forecasting. Pastikan server Flask berjalan di http://127.0.0.1:5000');
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            return back()->with('error', 'Terjadi kesalahan saat mengirim data ke layanan forecasting: ' . $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
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
}
