<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;  // Pastikan modelnya sesuai
use Illuminate\Support\Facades\Auth;

class PesananController extends Controller
{
    public function detail($id)
    {
        $query = Transaksi::with('customer')->where('IdTransaksi', $id);

        if (! Auth::user()?->isAdmin()) {
            $query->where('id_customer', Auth::id());
        }

        $pesanan = $query->firstOrFail();

        return view('toko.detail_pesanan', compact('pesanan'));
    }

    public function show($id)
    {
        $query = Transaksi::with(['customer', 'detailTransaksi.produk'])->where('IdTransaksi', $id);

        if (! Auth::user()?->isAdmin()) {
            $query->where('id_customer', Auth::id());
        }

        $pesanan = $query->firstOrFail();

        return view('nama-folder.detail-pesanan', compact('pesanan'));
    }
}
