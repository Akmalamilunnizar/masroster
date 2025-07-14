<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Transaksi;  // Pastikan modelnya sesuai
use App\Models\User;

class PesananController extends Controller
{
    public function detail($id)
    {
        $pesanan = Transaksi::with('user')->findOrFail($id);
        return view('toko.detail_pesanan', compact('pesanan'));
    }
    public function show($id)
    {
        $pesanan = Transaksi::with(['produk', 'user', 'address'])->findOrFail($id);
        return view('nama-folder.detail-pesanan', compact('pesanan'));
    }


}
