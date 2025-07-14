<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User; // Keep if you use User model directly for other methods
use App\Models\Transaksi;
use Illuminate\Support\Facades\Validator; // Keep if you use validation in other methods
use Illuminate\Support\Facades\DB; // Keep if you use raw DB queries in other methods
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon; // Keep if you use Carbon for date manipulations

class TransaksiController extends Controller
{
    /**
     * Menampilkan daftar transaksi dengan fitur filter bulan/tahun dan pencarian.
     * Menggunakan eager loading untuk relasi 'detail'.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $bulan = $request->query('bulan');
        $tahun = $request->query('tahun');
        $search = $request->input('search');
        $status_pesanan = $request->input('status_pesanan'); // Add status_pesanan filter

        // Mulai query Transaksi dengan eager loading 'detail'
        $query = Transaksi::with('detail');

        // Filter jika ada bulan
        if ($bulan) {
            $query->whereMonth('tglTransaksi', $bulan);
        }

        // Filter jika ada tahun
        if ($tahun) {
            $query->whereYear('tglTransaksi', $tahun);
        }

        // Filter jika ada status pesanan
        if ($status_pesanan) {
            $query->where('StatusPesanan', $status_pesanan);
        }

        // Filter jika ada pencarian
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('IdTransaksi', 'like', "%{$search}%")
                  ->orWhereHas('detail', function ($qDetail) use ($search) {
                      $qDetail->where('f_name', 'like', "%{$search}%");
                  });
            });
        }

        // Urutkan dan ambil data
        $transaksi = $query->orderBy('tglTransaksi', 'desc')->get();

        // Kirim data ke view
        return view('admin.allTransaksi', compact('transaksi', 'bulan', 'tahun', 'search', 'status_pesanan'));
    }

    /**
     * Metode untuk menerima orderan transaksi.
     * Menggunakan POST request.
     *
     * @param string $id ID dari transaksi yang akan diterima.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function terimaOrderan($id)
    {
        $transaksi = Transaksi::find($id);

        if (!$transaksi) {
            return redirect()->route('alltransaksi')->with('error', 'Transaksi tidak ditemukan.');
        }

        // Ubah status pesanan menjadi 'Diterima'
        $transaksi->StatusPesanan = 'Diterima';
        $transaksi->save();

        return redirect()->route('alltransaksi')->with('message', 'Orderan berhasil diterima!');
    }

    /**
     * Metode untuk menolak orderan transaksi.
     * Menggunakan POST request.
     *
     * @param string $id ID dari transaksi yang akan ditolak.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function tolakOrderan($id)
    {
        $transaksi = Transaksi::find($id);

        if (!$transaksi) {
            return redirect()->route('alltransaksi')->with('error', 'Transaksi tidak ditemukan.');
        }

        // Ubah status pesanan menjadi 'Ditolak'
        $transaksi->StatusPesanan = 'Ditolak';
        $transaksi->save();

        return redirect()->route('alltransaksi')->with('message', 'Orderan berhasil ditolak!');
    }

    /**
     * Mengekspor data transaksi ke PDF dengan filter bulan/tahun.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function exportPdf(Request $request)
    {
        $bulan = $request->query('bulan');
        $tahun = $request->query('tahun');

        $query = Transaksi::query()->with('detail'); // Eager load detail juga untuk PDF

        if ($bulan) {
            $query->whereMonth('tglTransaksi', $bulan); // Pastikan kolom tanggal transaksi yang benar
        }

        if ($tahun) {
            $query->whereYear('tglTransaksi', $tahun); // Pastikan kolom tanggal transaksi yang benar
        }

        $transaksis = $query->orderBy('tglTransaksi', 'asc')->get();

        $pdf = Pdf::loadView('admin.transaksi_pdf', compact('transaksis', 'bulan', 'tahun'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('laporan-transaksi.pdf');
    }

    public function showTransaction($id)
    {
        $transaksi = Transaksi::with(['user', 'address', 'detailTransaksi.produk', 'detailTransaksi.size'])
            ->where('IdTransaksi', $id)
            ->firstOrFail();

        return view('admin.transaction_details', compact('transaksi'));
    }

    public function ViewOrder($id)
    {
        $orders = Transaksi::with(['user', 'address', 'detailTransaksi.produk', 'detailTransaksi.size'])
            ->where('IdTransaksi', $id)
            ->firstOrFail();

        return view('admin.vieworder', compact('orders'));
    }

    // Catatan: Jika ada metode lain seperti ManageTransaksi, AddTransaksi, StoreTransaksi,
    // EditTransaksi, UpdateTransaksi, DeleteTransaksi, tambahkan di sini sesuai kebutuhan.
}