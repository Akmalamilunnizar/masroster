<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB; // Jika Anda menggunakan Query Builder
use App\Models\Produk;
use App\Models\Size;

class DetailProdukController extends Controller
{
    public function show(string $id): View
    {
        $produk = Produk::with(['sizes.satuan'])->findOrFail($id);

        // Get description from database
        $description = $produk->deskripsi ?? 'Produk berkualitas dengan hasil cetak yang memukau. Tersedia dalam berbagai ukuran dan finishing.';

        // Get user data if authenticated
        $user = null;
        $userPhone = '';
        if (auth()->check()) {
            $user = auth()->user();
            $userPhone = $user->nomor_telepon ?? '';
        }

        return view('admin.DetailProduk', [
            'produk' => $produk,
            'userPhone' => $userPhone,
            'description' => $description,
            'user' => $user
        ]);
    }

    public function create()
    {
        $ukuranProduk = DB::table('produk')
            ->whereNotNull('ukuran_produk')
            ->where('ukuran_produk', '!=', '0')
            ->pluck('ukuran_produk')
            ->toArray();

        $ukuranList = collect($ukuranProduk)
            ->flatMap(function ($item) {
                return explode(',', $item);
            })
            ->map(fn($item) => trim($item))
            ->unique()
            ->values()
            ->toArray();

        return view('admin.addproduk', compact('ukuranList'));
    }
}