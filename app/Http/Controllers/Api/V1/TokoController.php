<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB; //buat manggil database
use Illuminate\Support\Facades\Auth;

class TokoController extends Controller
{
    //
    public function tokodashboard(Request $request)
    {
        $query = Produk::query();

        if ($request->has('search') && $request->search != '') {
            $query->where('NamaProduk', 'LIKE', '%' . $request->search . '%');
        }
        // Contoh mengambil data produk terlaris, sesuaikan dengan logika bisnismu
        $produkTerlaris = Produk::take(3)->get();
        $produk = $query->get();

        return view('toko.dashboardToko', compact('produk', 'produkTerlaris'));
    }
    // public function cart()
    // {
    //     return view('toko.cart'); // arahkan ke view cart yang kamu buat
    // }
    public function cart()
    {
        $produk = Produk::all(); // atau query yang sesuai kebutuhan
        return view('toko.cart', compact('produk'));
    }
    public function pesanan()
    {
        if (Auth::user()->user === 'Admin') {
            $transaksi = Transaksi::orderBy('tglTransaksi', 'desc')->get();
        } else {
            $username = Auth::user()->username;
            $transaksi = Transaksi::where('username', $username)
                            ->orderBy('tglTransaksi', 'desc')
                            ->get();
        }

        return view('toko.pesanan', compact('transaksi'));
    }



}
