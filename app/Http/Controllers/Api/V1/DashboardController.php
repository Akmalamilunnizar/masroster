<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Items;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class DashboardController extends Controller
{

    public function Index()
    {
        // Get all items
        $items = Items::all();

        // Get items count by type (using IdJenisBarang)
        $itemsByType = Items::select('IdJenisBarang', DB::raw('count(*) as total'))
            ->groupBy('IdJenisBarang')
            ->get();

        // Get items with low stock (less than 10)
        $lowStockItems = Items::where('JumlahStok', '<', 10)->get();

        // Get total items count
        $totalItems = Items::count();

        // Get items with highest stock
        $topStockItems = Items::orderBy('JumlahStok', 'desc')
            ->take(5)
            ->get();

        return view("admin.dashboard", compact(
            'items',
            'itemsByType',
            'lowStockItems',
            'totalItems',
            'topStockItems'
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
