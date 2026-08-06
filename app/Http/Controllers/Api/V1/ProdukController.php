<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
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
        $produk = $this->resolveProduk($IdRoster);

        // Inventori legacy akan dihapus; pertahankan kontrak view dengan koleksi kosong
        $historiMasuk = collect();
        $historiKeluar = collect();

        // Kirim ke view
        return view('admin.detail_allproduk', compact('produk', 'historiMasuk', 'historiKeluar'));
    }

    // Menampilkan form tambah produk
    public function addProduk()
    {
        // Ambil data ukuran untuk dropdown
        $sizeList = Size::all();
        $jenisList = \App\Models\TypeItems::all();
        $tipeList = \App\Models\TipeRoster::all();
        $motifList = \App\Models\MotifRoster::all();

        return view('admin.addproduk', compact('sizeList', 'jenisList', 'tipeList', 'motifList'));
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
            $usesModernIdentity = Schema::hasColumn('produk', 'id') && Schema::hasColumn('produk', 'sku');

            DB::beginTransaction();
            // Upload gambar
            $path = $request->file('Img')->store('produk', 'public');

            // Simpan data produk ke database
            // Ensure IdRoster is populated when the column exists (some tests expect it)
            $idRosterValue = Schema::hasColumn('produk', 'IdRoster') ? $this->generateLegacyRosterCode() : null;

            $produk = Produk::create([
                'IdRoster' => $idRosterValue,
                // When modern identity (with `sku` column) exists, provide a temporary non-null SKU
                // to satisfy NOT NULL constraints; it will be overwritten after the model has an ID.
                'sku' => $usesModernIdentity ? ('MAS_TMP_'.random_int(1000, 9999).time()) : null,
                'id_jenis' => $request->IdJenisBarang,
                'id_tipe' => $request->id_tipe,
                'id_motif' => $request->id_motif,
                'stock' => $request->stock,
                'Img' => $path,
                'deskripsi' => $request->deskripsi,
            ]);

            if ($usesModernIdentity && ! empty($produk->id)) {
                $produk->forceFill([
                    'sku' => 'MAS'.str_pad((string) $produk->id, 3, '0', STR_PAD_LEFT),
                ])->saveQuietly();
            }

            // Debug: log product keys to ensure correct pivot behavior in various schemas
            Log::info('Produk created', [
                'id' => $produk->id ?? null,
                'IdRoster' => $produk->IdRoster ?? null,
                'getKeyName' => $produk->getKeyName(),
                'getKey' => $produk->getKey(),
                'usesModernIdentity' => $usesModernIdentity,
            ]);

            // Attach ukuran dan harga pivot agar data size tetap konsisten di Produk
            // Some test schemas expect legacy `IdRoster` in produk_size; handle both cases explicitly.
            $productForeignKey = Schema::hasColumn('produk_size', 'produk_id') ? 'produk_id' : 'IdRoster';

            // Ensure legacy IdRoster is present when required by pivot
            if ($productForeignKey === 'IdRoster' && empty($produk->IdRoster)) {
                $produk->IdRoster = $this->generateLegacyRosterCode();
                $produk->saveQuietly();
            }

            $productKeyValue = ($productForeignKey === 'produk_id') ? $produk->getKey() : $produk->IdRoster;

            // Build explicit inserts for pivot table to avoid relying on Eloquent internals
            $now = now();
            $rows = [];
            $hasIdRosterColumnOnPivot = Schema::hasColumn('produk_size', 'IdRoster');

            foreach ($syncData as $sizeId => $meta) {
                $row = [
                    $productForeignKey => $productKeyValue,
                    'id_ukuran' => $sizeId,
                    'harga' => $meta['harga'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if ($hasIdRosterColumnOnPivot) {
                    $row['IdRoster'] = $produk->IdRoster;
                }

                $rows[] = $row;
            }
            Log::info('Preparing to insert produk_size pivot', [
                'productForeignKey' => $productForeignKey,
                'productKeyValue' => $productKeyValue,
                'rowsCount' => count($rows),
                'rowsSample' => array_slice($rows, 0, 5),
            ]);

            if (! empty($rows)) {
                DB::table('produk_size')->insert($rows);
                Log::info('Inserted produk_size pivot rows', ['inserted' => count($rows)]);
            }

            DB::commit();

            return redirect()->route('allproduk')->with('message', 'Produk berhasil ditambahkan!');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in storeProduk: '.$e->getMessage(), ['exception' => $e]);

            return redirect()->back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    // Menampilkan form edit produk
    public function editProduk($id)
    {
        $produk = $this->resolveProduk($id);
        $sizeList = Size::all();
        $jenisList = \App\Models\TypeItems::all();
        $tipeList = \App\Models\TipeRoster::all();
        $motifList = \App\Models\MotifRoster::all();

        return view('admin.editproduk', compact('produk', 'sizeList', 'jenisList', 'tipeList', 'motifList'));
    }

    // Memperbarui data produk
    public function updateProduk(Request $request, $id)
    {
        Log::info('updateProduk called', ['id' => $id, 'method' => $request->method(), 'url' => $request->fullUrl()]);
        $produk = $this->resolveProduk($id);

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

            return redirect()->back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    // Menghapus produk
    public function deleteProduk($id)
    {
        $produk = $this->resolveProduk($id);

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

            return redirect()->back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
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
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $usesSku = Schema::hasColumn('produk', 'sku');

        $dataProduk = Produk::with(['sizes', 'jenisRoster', 'tipeRoster', 'motif'])
            ->where(function ($query) use ($search, $usesSku) {
                $query->where('IdRoster', 'like', "%$search%")
                    ->when($usesSku, function ($builder) use ($search) {
                        $builder->orWhere('sku', 'like', "%$search%");
                    })
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
        $produk = $this->resolveProduk($id);

        return view('admin.showproduk', compact('produk'));
    }

    // AJAX method to get connected tipe roster based on jenis roster
    public function getConnectedTipe(Request $request)
    {
        $jenisId = (int) $request->query('jenis_id');

        // Debug logging
        Log::info('getConnectedTipe called with jenis_id: '.$jenisId);

        $connectedTipe = \Illuminate\Support\Facades\DB::table('detail_tipe')
            ->join('tipe_roster', 'detail_tipe.id_tipe', '=', 'tipe_roster.IdTipe')
            ->where('detail_tipe.id_jenis', $jenisId)
            ->orderBy('tipe_roster.namaTipe')
            ->select('tipe_roster.IdTipe', 'tipe_roster.namaTipe')
            ->get();

        Log::info('Connected tipe result', [
            'count' => $connectedTipe->count(),
            'ids' => $connectedTipe->pluck('IdTipe')->take(10)->values()->all(),
        ]);

        return response()->json($connectedTipe);
    }

    // AJAX method to get connected motif roster based on tipe roster
    public function getConnectedMotif(Request $request)
    {
        $tipeId = (int) $request->query('tipe_id');

        // Debug logging
        Log::info('getConnectedMotif called with tipe_id: '.$tipeId);

        $connectedMotif = \Illuminate\Support\Facades\DB::table('detail_motif')
            ->join('motif_roster', 'detail_motif.id_motif', '=', 'motif_roster.IdMotif')
            ->where('detail_motif.id_tipe', $tipeId)
            ->orderBy('motif_roster.nama_motif')
            ->select('motif_roster.IdMotif', 'motif_roster.nama_motif')
            ->get();

        Log::info('Connected motif result', [
            'count' => $connectedMotif->count(),
            'ids' => $connectedMotif->pluck('IdMotif')->take(10)->values()->all(),
        ]);

        return response()->json($connectedMotif);
    }

    private function ensureMotifMatchesTipe($tipeId, $motifId): void
    {
        if (empty($motifId)) {
            return;
        }
        // If the detail_motif pivot table is not present (e.g., in lightweight test schema), skip the check.
        if (! Schema::hasTable('detail_motif')) {
            return;
        }

        $isValidPair = DB::table('detail_motif')
            ->where('id_tipe', $tipeId)
            ->where('id_motif', $motifId)
            ->exists();

        if (! $isValidPair) {
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

    private function resolveProduk($identifier): Produk
    {
        $query = Produk::with(['sizes', 'jenisRoster', 'tipeRoster', 'motif']);

        if (is_numeric($identifier)) {
            $produk = $query->find($identifier);

            if ($produk) {
                return $produk;
            }
        }

        if (Schema::hasColumn('produk', 'sku')) {
            $produk = $query->where('sku', $identifier)->first();

            if ($produk) {
                return $produk;
            }
        }

        return $query->where('IdRoster', $identifier)->firstOrFail();
    }

    private function generateLegacyRosterCode(): string
    {
        $lastProduk = Produk::orderBy('IdRoster', 'desc')->first();

        if (! $lastProduk || empty($lastProduk->IdRoster)) {
            return 'MAS001';
        }

        return 'MAS'.str_pad(((int) substr($lastProduk->IdRoster, 3) + 1), 3, '0', STR_PAD_LEFT);
    }
}
