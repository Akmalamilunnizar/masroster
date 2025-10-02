<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Items;
use App\Models\Produk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class DashboardController extends Controller
{

    public function Index()
    {
        // Get all items
        $roster = Items::all();

        // Get items count by type (group by id_jenis) and load relation for labels
        // Use LEFT JOIN to show all types, even those with 0 products
        $itemsByType = DB::table('jenisbarang')
            ->leftJoin('produk', 'jenisbarang.IdJenisBarang', '=', 'produk.id_jenis')
            ->select('jenisbarang.IdJenisBarang as id_jenis', 'jenisbarang.JenisBarang', DB::raw('COALESCE(COUNT(produk.IdRoster), 0) as total'))
            ->groupBy('jenisbarang.IdJenisBarang', 'jenisbarang.JenisBarang')
            ->orderBy('jenisbarang.JenisBarang')
            ->get();

        // Get items with low stock (less than 10)
        $lowStockItems = Produk::where('JumlahStok', '<', 10)->get();

        // Get total items count
        $totalItems = Produk::count();

        // Get items with highest stock (only products with stock > 0)
        $topStockRoster = Produk::where('JumlahStok', '>', 0)
            ->orderBy('JumlahStok', 'desc')
            ->take(5)
            ->get();

        // Orders and revenue summary
        $totalOrders = DB::table('transaksi')->count();
        $totalRevenue = (int) DB::table('transaksi')->sum('GrandTotal');

        // Revenue by month (last 6 months) - show all months even with 0 revenue
        $revenueByMonth = collect();
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $revenue = DB::table('transaksi')
                ->whereRaw('DATE_FORMAT(tglTransaksi, "%Y-%m") = ?', [$month])
                ->sum('GrandTotal') ?? 0;
            
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
            'topSelling'
        ));
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
