<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Items;
use App\Models\Size;
use App\Models\DetailMasuk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\BarangMasuk;
use App\Models\DetailKeluar;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ProdukController extends Controller
{
    // Menampilkan semua produk
    public function index()
    {
        // Load products with relationships needed for listing
        $dataProduk = Produk::with(['sizes', 'jenisRoster', 'motif'])->get();
        return view('admin.allproduk', compact('dataProduk'));
    }
    public function detail($IdRoster)
    {
        // Ambil data barang dengan relasi yang dibutuhkan
        $item = Items::with(['jenisRoster', 'satuan'])
            ->where('IdRoster', $IdRoster)
            ->firstOrFail();

        // Ambil histori detail barang masuk (DetailMasuk) yang terkait barang ini, terbaru dulu
        $historiMasuk = DetailMasuk::with('supplier')
            ->where('IdRoster', $IdRoster)
            ->orderBy('created_at', 'desc')
            ->get();

        // Ambil histori detail barang keluar dari tabel detail_barangkeluar
        $historiKeluar = DB::table('detail_barangkeluar as dbk')
            ->join('barangkeluar as bk', 'dbk.IdKeluar', '=', 'bk.IdKeluar')
            ->select('dbk.*', 'bk.tglKeluar')
            ->where('dbk.IdRoster', $IdRoster)
            ->orderBy('bk.tglKeluar', 'desc')
            ->get();

        // Kirim ke view
        return view('admin.detail_allitems', compact('item', 'historiMasuk', 'historiKeluar'));
    }
    // Menampilkan form tambah produk
    public function addProduk()
    {
        // Ambil ID produk terakhir dari database
        $lastProduk = Produk::orderBy('IdRoster', 'desc')->first();
        $newId = $lastProduk ? 'MAS' . str_pad((substr($lastProduk->IdRoster, 3) + 1), 3, '0', STR_PAD_LEFT) : 'MAS001';

        // Ambil data ukuran untuk dropdown
        $sizeList = Size::all();
        $jenisList = \App\Models\TypeItems::all();
        $tipeList = \App\Models\TipeRoster::all();
        $motifList = \App\Models\MotifRoster::all();

        return view('admin.addproduk', compact('newId', 'sizeList', 'jenisList', 'tipeList', 'motifList'));
    }

    // Menyimpan produk baru
    public function storeProduk(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'sizes' => 'required|array',
                'sizes.*' => 'exists:size,id_ukuran',
                'harga_per_size' => 'required|array',
                'harga_per_size.*' => 'required|integer',
                'IdJenisBarang' => 'required|exists:jenisbarang,IdJenisBarang',
                'id_tipe' => 'required|exists:tipe_roster,IdTipe',
                'id_motif' => [
                    'nullable',
                    'exists:motif_roster,IdMotif',
                ],
                'stock' => 'required|integer|min:0',
                'Img' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'deskripsi' => 'required|string|max:1500',
            ]);

            DB::beginTransaction();
            // Ambil ID produk terakhir dari database
            $lastProduk = Produk::orderBy('IdRoster', 'desc')->first();
            $newId = $lastProduk ? 'MAS' . str_pad((substr($lastProduk->IdRoster, 3) + 1), 3, '0', STR_PAD_LEFT) : 'MAS001';

            // Upload gambar
            $path = $request->file('Img')->store('produk', 'public');

            // Simpan data produk ke database
            $produk = Produk::create([
                'IdRoster' => $newId,
                'id_jenis' => $request->IdJenisBarang,
                'id_tipe' => $request->id_tipe,
                'id_motif' => $request->id_motif,
                'stock' => $request->stock,
                'Img' => $path,
                'deskripsi' => $request->deskripsi,
            ]);

            // Attach sizes with harga
            $syncData = [];
            foreach ($request->sizes as $index => $sizeId) {
                $syncData[$sizeId] = ['harga' => $request->harga_per_size[$index]];
            }
            $produk->sizes()->attach($syncData);

            DB::commit();
            return redirect()->route('allproduk')->with('message', 'Produk berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // Menampilkan form edit produk
    public function editProduk($id)
    {
        $produk = Produk::with(['sizes', 'jenisRoster', 'tipeRoster', 'motif'])->findOrFail($id);
        $sizeList = Size::all();
        $jenisList = \App\Models\TypeItems::all();
        $tipeList = \App\Models\TipeRoster::all();
        $motifList = \App\Models\MotifRoster::all();
        return view('admin.editproduk', compact('produk', 'sizeList', 'jenisList', 'tipeList', 'motifList'));
    }

    // Memperbarui data produk
    public function updateProduk(Request $request, $id)
    {
        $produk = Produk::findOrFail($id);

        // Validasi input
        $request->validate([
            'sizes' => 'required|array',
            'sizes.*' => 'exists:size,id_ukuran',
            'IdJenisBarang' => 'required|exists:jenisbarang,IdJenisBarang',
            'id_tipe' => 'required|exists:tipe_roster,IdTipe',
            'id_motif' => [
                'nullable',
                'exists:motif_roster,IdMotif',
            ],
            'stock' => 'required|integer|min:0',
            'Img' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'deskripsi' => 'required|string|max:1500',
        ]);

        DB::beginTransaction();
        try {
            // Jika gambar baru diupload
            if ($request->hasFile('Img')) {
                if ($produk->Img && Storage::disk('public')->exists($produk->Img)) {
                    Storage::disk('public')->delete($produk->Img);
                }
                $path = $request->file('Img')->store('produk', 'public');
                $produk->Img = $path;
            }

            // Update data produk
            $produk->update([
                'id_jenis' => $request->IdJenisBarang,
                'id_tipe' => $request->id_tipe,
                'id_motif' => $request->id_motif,
                'stock' => $request->stock,
                'deskripsi' => $request->deskripsi,
            ]);

            // Sync sizes
            $syncData = [];
            foreach ($request->sizes as $index => $sizeId) {
                $syncData[$sizeId] = ['harga' => $request->harga_per_size[$index]];
            }
            $produk->sizes()->sync($syncData);

            DB::commit();
            return redirect()->route('allproduk')->with('message', 'Produk berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // Menghapus produk
    public function deleteProduk($id)
    {
        $produk = Produk::findOrFail($id);

        DB::beginTransaction();
        try {
            // Hapus gambar jika ada
            if ($produk->Img && Storage::disk('public')->exists($produk->Img)) {
                Storage::disk('public')->delete($produk->Img);
            }

            // Hapus relasi dengan size dari pivot table produk_size
            $produk->sizes()->detach();

            // Hapus data produk dari database
            $produk->delete();

            DB::commit();
            return redirect()->route('allproduk')->with('message', 'Produk berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function StoreItem(Request $request)
    {
        $request->validate([
            'IdRoster' => 'required|unique:databarang,IdRoster',
            'NamaBarang' => 'required|unique:databarang,NamaBarang',
            'IdJenisBarang' => 'required',
            'IdSatuan' => 'required',
            'IdMasuk' => 'required',
            'username' => 'required',
            'IdSupplier' => 'required',
            'QtyMasuk' => 'required|numeric',
            'HargaSatuan' => 'required|numeric',
            'SubTotal' => 'required|numeric',
        ]);

        // Simpan ke tabel databarang (timestamps auto)
        Items::create([
            'IdRoster' => $request->IdRoster,
            'NamaBarang' => $request->NamaBarang,
            'IdJenisBarang' => $request->IdJenisBarang,
            'stock' => 0, // handled by trigger
            'IdSatuan' => $request->IdSatuan,
        ]);

        // Simpan ke tabel barangmasuk (master transaksi, no timestamps)
        BarangMasuk::create([
            'IdMasuk' => $request->IdMasuk,
            'username' => $request->username,
            'tglMasuk' => Carbon::now(),
        ]);

        // Simpan ke tabel detail_barangmasuk (timestamps auto)
        DetailMasuk::create([
            'IdMasuk' => $request->IdMasuk,
            'IdSupplier' => $request->IdSupplier,
            'IdRoster' => $request->IdRoster,
            'QtyMasuk' => $request->QtyMasuk,
            'HargaSatuan' => $request->HargaSatuan,
            'SubTotal' => $request->SubTotal,
        ]);

        return redirect()->route('allitems')->with('message', 'Barang telah berhasil ditambah!');
    }

    public function StoreKeluarBarang(Request $request)
    {
        $request->validate([
            'IdKeluar' => 'required',
            'username' => 'required',
            'IdRoster' => 'required',
            'QtyKeluar' => 'required|numeric|min:1',
        ]);

        // Check if stock is sufficient
        $item = Items::findOrFail($request->IdRoster);
        if ($item->stock < $request->QtyKeluar) {
            return redirect()->back()->with('error', 'Stok tidak mencukupi!');
        }

        // Begin transaction
        DB::beginTransaction();
        try {
            // Insert into barangkeluar (no timestamps)
            \App\Models\BarangKeluar::create([
                'IdKeluar' => $request->IdKeluar,
                'username' => $request->username,
                'tglKeluar' => Carbon::now(),
            ]);

            // Insert into detail_barangkeluar (timestamps auto)
            DetailKeluar::create([
                'IdKeluar' => $request->IdKeluar,
                'IdRoster' => $request->IdRoster,
                'QtyKeluar' => $request->QtyKeluar,
            ]);

            DB::commit();
            return redirect()->route('allitems')->with('message', 'Barang berhasil keluar!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function tambahQty(Request $request)
    {
        $request->validate([
            'IdRoster' => 'required',
            'QtyMasuk' => 'required|integer|min:1',
        ]);

        // Ambil IdMasuk terakhir dari tabel barangmasuk
        $lastMasuk = DB::table('barangmasuk')->orderByDesc('IdMasuk')->first();

        // Buat IdMasuk baru berdasarkan yang terakhir
        $newIdMasuk = $lastMasuk
            ? 'BM' . str_pad((int) substr($lastMasuk->IdMasuk, 2) + 1, 4, '0', STR_PAD_LEFT)
            : 'BM0001';

        // Ambil data detail masuk terakhir untuk IdRoster ini untuk mendapatkan HargaSatuan terbaru
        $latestDetailMasuk = DetailMasuk::where('IdRoster', $request->IdRoster)
            ->orderBy('created_at', 'desc')
            ->first();

        $hargaSatuan = $latestDetailMasuk ? $latestDetailMasuk->HargaSatuan : 0;
        $subTotal = $request->QtyMasuk * $hargaSatuan;

        // Ambil IdSupplier dari detail masuk terakhir, atau gunakan default jika tidak ada
        $idSupplier = $latestDetailMasuk ? $latestDetailMasuk->IdSupplier : 'SP0001';

        // Simpan data ke tabel barangmasuk
        DB::table('barangmasuk')->insert([
            'IdMasuk' => $newIdMasuk,
            'username' => (Auth::check() ? Auth::user()->username : 'admin'),
            'tglMasuk' => now(),
        ]);

        // Simpan data ke tabel detail_barangmasuk
        DB::table('detail_barangmasuk')->insert([
            'IdMasuk' => $newIdMasuk,
            'IdSupplier' => $idSupplier,
            'IdRoster' => $request->IdRoster,
            'QtyMasuk' => $request->QtyMasuk,
            'HargaSatuan' => $hargaSatuan,
            'SubTotal' => $subTotal,
        ]);

        return redirect()->back()->with('message', 'Qty berhasil ditambahkan!');
    }

    public function DeleteItem($IdRoster)
    {
        // Ambil semua IdMasuk yang berkaitan dengan barang ini
        $idMasukList = DetailMasuk::where('IdRoster', $IdRoster)->pluck('IdMasuk');

        // Hapus semua entri detail masuk yang terkait dengan barang ini
        DetailMasuk::where('IdRoster', $IdRoster)->delete();

        // Hapus dari databarang
        Items::where('IdRoster', $IdRoster)->delete();

        // Cek apakah IdMasuk yang tadi sudah tidak digunakan lagi di detail_barangmasuk
        foreach ($idMasukList as $idMasuk) {
            $used = DetailMasuk::where('IdMasuk', $idMasuk)->exists();
            if (!$used) {
                BarangMasuk::where('IdMasuk', $idMasuk)->delete();
            }
        }

        return redirect()->route('allitems')->with('message', 'Penghapusan Barang ');
    }

    // Menampilkan list produk dalam format JSON
    public function get_produk_list()
    {
        $produk = Produk::with(['jenisRoster', 'motif'])->get();
        return response()->json($produk, 200);
    }

    // Fitur pencarian produk
    public function searchProduk(Request $request)
    {
        $search = $request->search;

        $dataProduk = Produk::with(['sizes', 'jenisRoster', 'motif'])
            ->where(function ($query) use ($search) {
                $query->where('IdRoster', 'like', "%$search%")
                    ->orWhere('NamaRoster', 'like', "%$search%")
                    ->orWhere('deskripsi', 'like', "%$search%");
            })
            ->get();

        return view('admin.allproduk', compact('dataProduk', 'search'));
    }

    // This method seems out of place for a ProdukController and refers to a Supplier model.
    // I've kept it as is, but you might want to move it to a SupplierController if it's meant for that.
    // Deprecated method; not used for Produk

    public function show($id)
    {
        $produk = Produk::with(['jenisRoster', 'motif'])->findOrFail($id);
        return view('admin.showproduk', compact('produk'));
    }
    public function sizes()
    {
        return $this->belongsToMany(Size::class, 'produk_size', 'IdRoster', 'id_ukuran')
            ->withPivot('harga')
            ->withTimestamps();
    }

    // AJAX method to get connected tipe roster based on jenis roster
    public function getConnectedTipe(Request $request)
    {
        $jenisId = (int) $request->query('jenis_id');

        // Debug logging
        Log::info('getConnectedTipe called with jenis_id: ' . $jenisId);

        $connectedTipe = \Illuminate\Support\Facades\DB::table('detail_tipe')
            ->join('tipe_roster', 'detail_tipe.id_tipe', '=', 'tipe_roster.IdTipe')
            ->where('detail_tipe.id_jenis', $jenisId)
            ->orderBy('tipe_roster.namaTipe')
            ->select('tipe_roster.IdTipe', 'tipe_roster.namaTipe')
            ->get();

        Log::info('Connected tipe result: ' . $connectedTipe->toJson());

        return response()->json($connectedTipe);
    }

    // AJAX method to get connected motif roster based on tipe roster
    public function getConnectedMotif(Request $request)
    {
        $tipeId = (int) $request->query('tipe_id');

        // Debug logging
        Log::info('getConnectedMotif called with tipe_id: ' . $tipeId);

        $connectedMotif = \Illuminate\Support\Facades\DB::table('detail_motif')
            ->join('motif_roster', 'detail_motif.id_motif', '=', 'motif_roster.IdMotif')
            ->where('detail_motif.id_tipe', $tipeId)
            ->orderBy('motif_roster.nama_motif')
            ->select('motif_roster.IdMotif', 'motif_roster.nama_motif')
            ->get();

        Log::info('Connected motif result: ' . $connectedMotif->toJson());

        return response()->json($connectedMotif);
    }
}
