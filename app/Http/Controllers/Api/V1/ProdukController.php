<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Size;
use App\Models\DetailMasuk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProdukController extends Controller
{
    // Menampilkan semua produk
    public function index()
    {
        // Load products with relationships needed for listing
        $dataProduk = Produk::with(['sizes', 'jenisRoster', 'tipeRoster', 'motif'])->get();
        return view('admin.allproduk', compact('dataProduk'));
    }
    public function detail($IdRoster)
    {
        // Ambil data produk dengan relasi yang dibutuhkan untuk detail dan ukuran
        $produk = Produk::with(['jenisRoster', 'tipeRoster', 'motif', 'sizes'])
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
        return view('admin.detail_allproduk', compact('produk', 'historiMasuk', 'historiKeluar'));
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
                'sizes' => 'required|array|min:1',
                'sizes.*' => 'exists:size,id_ukuran',
                'harga_per_size' => 'required|array|min:1',
                'harga_per_size.*' => 'required|integer|min:0',
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

            $this->ensureMotifMatchesTipe($request->id_tipe, $request->id_motif);
            $syncData = $this->buildSizeSyncData($request->sizes, $request->harga_per_size);

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

            // Attach ukuran dan harga pivot agar data size tetap konsisten di Produk
            $produk->sizes()->attach($syncData);

            DB::commit();
            return redirect()->route('allproduk')->with('message', 'Produk berhasil ditambahkan!');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
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
            'sizes' => 'required|array|min:1',
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

        $this->ensureMotifMatchesTipe($request->id_tipe, $request->id_motif);
        $syncData = $this->buildSizeSyncData($request->sizes, $request->harga_per_size);

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

            // Sinkronkan ukuran dan harga pivot agar update tidak meninggalkan pasangan data yang tidak seimbang
            $produk->sizes()->sync($syncData);

            DB::commit();
            return redirect()->route('allproduk')->with('message', 'Produk berhasil diperbarui!');
        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
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

    // Menampilkan list produk dalam format JSON
    public function get_produk_list()
    {
        $produk = Produk::with(['jenisRoster', 'tipeRoster', 'motif', 'sizes'])->get();
        return response()->json($produk, 200);
    }

    // Fitur pencarian produk
    public function searchProduk(Request $request)
    {
        $search = $request->search;

        $dataProduk = Produk::with(['sizes', 'jenisRoster', 'tipeRoster', 'motif'])
            ->where(function ($query) use ($search) {
                $query->where('IdRoster', 'like', "%$search%")
                    ->orWhere('NamaProduk', 'like', "%$search%")
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
        $produk = Produk::with(['sizes', 'jenisRoster', 'tipeRoster', 'motif'])->findOrFail($id);
        return view('admin.showproduk', compact('produk'));
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

    private function ensureMotifMatchesTipe($tipeId, $motifId): void
    {
        if (empty($motifId)) {
            return;
        }

        $isValidPair = DB::table('detail_motif')
            ->where('id_tipe', $tipeId)
            ->where('id_motif', $motifId)
            ->exists();

        if (!$isValidPair) {
            throw ValidationException::withMessages([
                'id_motif' => 'Motif yang dipilih tidak sesuai dengan tipe produk.',
            ]);
        }
    }

    private function buildSizeSyncData(array $sizes, array $prices): array
    {
        $sizes = array_values($sizes);
        $prices = array_values($prices);

        if (count($sizes) !== count($prices)) {
            throw ValidationException::withMessages([
                'harga_per_size' => 'Jumlah ukuran dan harga per ukuran harus sama.',
            ]);
        }

        if (count(array_unique($sizes)) !== count($sizes)) {
            throw ValidationException::withMessages([
                'sizes' => 'Ukuran produk tidak boleh duplikat.',
            ]);
        }

        $syncData = [];

        foreach ($sizes as $index => $sizeId) {
            $syncData[$sizeId] = ['harga' => $prices[$index]];
        }

        return $syncData;
    }
}
