<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Items;
use App\Models\Produk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;

class DashboardController extends Controller
{

    public function Index()
    {
        // Get all items
        $roster = Schema::hasTable('items') ? Items::all() : collect();

        // Get items count by type (group by id_jenis) and load relation for labels
        // Use LEFT JOIN to show all types, even those with 0 products
        $itemsByType = DB::table('jenisbarang')
            ->leftJoin('produk', 'jenisbarang.IdJenisBarang', '=', 'produk.id_jenis')
            ->select('jenisbarang.IdJenisBarang as id_jenis', 'jenisbarang.JenisBarang', DB::raw('COALESCE(COUNT(produk.IdRoster), 0) as total'))
            ->groupBy('jenisbarang.IdJenisBarang', 'jenisbarang.JenisBarang')
            ->orderBy('jenisbarang.JenisBarang')
            ->get();

        // Get items with low stock (legacy quick metric)
        $lowStockItems = Produk::where('stock', '<', 10)->get();

        $forecastProducts = Produk::select([
            'IdRoster',
            'NamaProduk',
            'stock',
            'forecasted_demand',
            'safety_stock',
            'forecast_status',
            'last_forecast_at',
        ])->get();

        $statusSummary = [
            'critical' => 0,
            'low' => 0,
            'safe' => 0,
            'overstock' => 0,
        ];

        $restockRecommendations = collect();

        foreach ($forecastProducts as $product) {
            $safetyStock = (int) ($product->safety_stock ?? 70);
            $forecastedDemand = (float) ($product->forecasted_demand ?? 0);
            $status = $this->calculateStockStatus((int) $product->stock, $forecastedDemand, $safetyStock, $product->forecast_status);

            $statusSummary[$status]++;

            if (in_array($status, ['critical', 'low'], true)) {
                $recommendedQty = (int) ceil(($forecastedDemand + $safetyStock) - (int) $product->stock);

                $restockRecommendations->push([
                    'IdRoster' => $product->IdRoster,
                    'NamaProduk' => $product->NamaProduk,
                    'stock' => (int) $product->stock,
                    'forecasted_demand' => $forecastedDemand,
                    'safety_stock' => $safetyStock,
                    'status' => $status,
                    'recommended_qty' => max(1, $recommendedQty),
                ]);
            }
        }

        $restockRecommendations = $restockRecommendations
            ->sortBy([
                fn (array $item): int => $item['status'] === 'critical' ? 0 : 1,
                fn (array $item): int => $item['stock'],
            ])
            ->values()
            ->take(8);

        // Get total items count
        $totalItems = Produk::count();

        // Get items with highest stock (only products with stock > 0)
        $topStockRoster = Produk::where('stock', '>', 0)
            ->orderBy('stock', 'desc')
            ->take(5)
            ->get();

        // Orders and revenue summary
        $totalOrders = DB::table('transaksi')->count();
        $totalRevenue = (int) DB::table('transaksi')->sum('GrandTotal');

        // Revenue by month (last 6 months) - show all months even with 0 revenue
        $revenueByMonth = collect();
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $query = DB::table('transaksi');
            if (DB::connection()->getDriverName() === 'sqlite') {
                $query->whereRaw("strftime('%Y-%m', tglTransaksi) = ?", [$month]);
            } else {
                $query->whereRaw('DATE_FORMAT(tglTransaksi, "%Y-%m") = ?', [$month]);
            }
            $revenue = $query->sum('GrandTotal') ?? 0;

            $revenueByMonth->push([
                'ym' => $month,
                'total' => (int) $revenue
            ]);
        }

        // Top selling products by quantity
        $topSelling = DB::table('detail_transaksi')
            ->select('IdRoster', DB::raw('SUM(QtyProduk) as total_qty'))
            ->groupBy('IdRoster')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        // If no sales data, show sample data for demonstration
        if ($topSelling->isEmpty()) {
            $topSelling = collect([
                (object)['IdRoster' => 'MAS001', 'total_qty' => 0],
                (object)['IdRoster' => 'Sample1', 'total_qty' => 0],
                (object)['IdRoster' => 'Sample2', 'total_qty' => 0],
                (object)['IdRoster' => 'Sample3', 'total_qty' => 0],
                (object)['IdRoster' => 'Sample4', 'total_qty' => 0],
            ]);
        }

        return view("admin.dashboard", compact(
            'roster',
            'itemsByType',
            'lowStockItems',
            'totalItems',
            'topStockRoster',
            'totalOrders',
            'totalRevenue',
            'revenueByMonth',
            'topSelling',
            'statusSummary',
            'restockRecommendations'
        ));
    }

    private function calculateStockStatus(int $stock, float $forecastedDemand, int $safetyStock, ?string $storedStatus = null): string
    {
        if (in_array($storedStatus, ['critical', 'low', 'safe', 'overstock'], true)) {
            return $storedStatus;
        }

        if ($stock < $safetyStock) {
            return 'critical';
        }

        if ($stock < ($forecastedDemand + $safetyStock)) {
            return 'low';
        }

        if ($stock > (($forecastedDemand + $safetyStock) * 3)) {
            return 'overstock';
        }

        return 'safe';
    }

    public function AdminLogout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public static function ubahAngkaToBulan($bulanAngka)
    {
        $bulanArray = [
            '0' => '',
            '1' => 'Januari',
            '2' => 'Februari',
            '3' => 'Maret',
            '4' => 'April',
            '5' => 'Mei',
            '6' => 'Juni',
            '7' => 'Juli',
            '8' => 'Agustus',
            '9' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];
        return $bulanArray[$bulanAngka + 0];
    }
}
