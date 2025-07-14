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
        return view('admin.forecast.form');
    }

    public function getSalesData()
    {
        try {
            Log::info('Fetching sales data for forecasting');
            
            // Get the last 12 months of sales data
            $salesData = Transaksi::select(
                DB::raw('DATE_FORMAT(tanggal, "%Y-%m") as bulan'),
                DB::raw('COALESCE(SUM(jumlah), 0) as terjual')
            )
            ->where('tanggal', '>=', Carbon::now()->subMonths(12))
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

            Log::info('Raw sales data:', ['data' => $salesData->toArray()]);

            // If we don't have enough data, generate sample data
            if ($salesData->count() < 12) {
                Log::info('Not enough data, generating sample data');
                $salesData = collect();
                $currentDate = Carbon::now();
                
                for ($i = 0; $i < 12; $i++) {
                    $date = $currentDate->copy()->subMonths($i);
                    $salesData->push([
                        'bulan' => $date->format('Y-m'),
                        'terjual' => rand(50, 150) // Random sales between 50-150
                    ]);
                }
                
                $salesData = $salesData->sortBy('bulan')->values();
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
                'terjual.*' => 'required|numeric'
            ]);

            $client = new Client([
                'timeout' => 30,
                'connect_timeout' => 30
            ]);

            $data = [
                'bulan' => $request->input('bulan'),
                'terjual' => $request->input('terjual')
            ];

            $response = $client->post('http://127.0.0.1:5000/predict', [
                'json' => $data,
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ]
            ]);

            $body = $response->getBody();
            $result = json_decode($body);

            if (!$result) {
                throw new \Exception('Invalid response from forecasting service');
            }

            return view('admin.forecast.result', ['result' => $result]);

        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            return back()->with('error', 'Tidak dapat terhubung ke layanan forecasting. Pastikan server Flask berjalan di http://127.0.0.1:5000');
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            return back()->with('error', 'Terjadi kesalahan saat mengirim data ke layanan forecasting: ' . $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
