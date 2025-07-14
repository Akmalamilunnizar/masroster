<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Items;
use App\Models\Size;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProdukController extends Controller
{
    // Menampilkan semua produk
    public function index()
    {
        $dataProduk = Produk::with(['bahan', 'sizes.satuan'])->get();
        return view('admin.allproduk', compact('dataProduk'));
    }

    // Menampilkan form tambah produk
    public function addProduk()
    {
        // Ambil ID produk terakhir dari database
        $lastProduk = Produk::orderBy('IdProduk', 'desc')->first();
        $newId = $lastProduk ? 'P' . str_pad((substr($lastProduk->IdProduk, 1) + 1), 4, '0', STR_PAD_LEFT) : 'P0001';

        // Ambil data bahan dan ukuran untuk dropdown
        $bahanList = Items::with('jenisBarang')->get();
        $sizeList = Size::with('satuan')->get();

        return view('admin.addproduk', compact('newId', 'bahanList', 'sizeList'));
    }

    // Menyimpan produk baru
    public function storeProduk(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'NamaProduk' => 'required',
                'sizes' => 'required|array',
                'sizes.*' => 'exists:size,id_ukuran',
                'harga_per_size' => 'required|array',
                'harga_per_size.*' => 'required|integer',
                'custom_harga' => 'required|integer',
                'id_bahan' => 'nullable|exists:databarang,IdBarang',
                'Img' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'deskripsi' => 'required|string|max:1500',
            ]);

            DB::beginTransaction();
            // Ambil ID produk terakhir dari database
            $lastProduk = Produk::orderBy('IdProduk', 'desc')->first();
            $newId = $lastProduk ? 'P' . str_pad((substr($lastProduk->IdProduk, 1) + 1), 4, '0', STR_PAD_LEFT) : 'P0001';

            // Upload gambar
            $path = $request->file('Img')->store('produk', 'public');

            // Simpan data produk ke database
            $produk = Produk::create([
                'IdProduk' => $newId,
                'NamaProduk' => $request->NamaProduk,
                'custom_harga' => $request->custom_harga,
                'id_bahan' => $request->id_bahan,
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
        $produk = Produk::with(['bahan', 'sizes.satuan'])->findOrFail($id);
        $bahanList = Items::all();
        $sizeList = Size::with('satuan')->get();
        return view('admin.editproduk', compact('produk', 'bahanList', 'sizeList'));
    }

    // Memperbarui data produk
    public function updateProduk(Request $request, $id)
    {
        $produk = Produk::findOrFail($id);

        // Validasi input
        $request->validate([
            'NamaProduk' => 'required',
            'sizes' => 'required|array',
            'sizes.*' => 'exists:size,id_ukuran',
            'custom_harga' => 'required|integer',
            'id_bahan' => 'nullable|exists:databarang,IdBarang',
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
                'NamaProduk' => $request->NamaProduk,
                'custom_harga' => $request->custom_harga,
                'id_bahan' => $request->id_bahan,
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
        $produk = Produk::with(['bahan'])->get();
        return response()->json($produk, 200);
    }

    // Fitur pencarian produk
    public function searchProduk(Request $request)
    {
        $search = $request->search;

        $dataProduk = Produk::with(['bahan'])
            ->leftJoin('databarang', 'produk.id_bahan', '=', 'databarang.IdBarang')
            ->where(function ($query) use ($search) {
                $query->where('IdProduk', 'like', "%$search%")
                    ->orWhere('NamaProduk', 'like', "%$search%")
                    ->orWhere('HargaProduk', 'like', "%$search%")
                    ->orWhere('sizes.id_ukuran', 'like', "%$search%")
                    ->orWhere('id_bahan', 'like', "%$search%")
                    ->orWhere('deskripsi', 'like', "%$search%")
                    ->orWhere('databarang.NamaBarang', 'like', "%$search%");
            })
            ->select('produk.*')
            ->get();

        return view('admin.allproduk', compact('dataProduk', 'search'));
    }

    // This method seems out of place for a ProdukController and refers to a Supplier model.
    // I've kept it as is, but you might want to move it to a SupplierController if it's meant for that.
    public function destroy($id)
    {
        $supplier = Supplier::find($id); // Assuming Supplier model is imported or in the same namespace

        if (!$supplier) {
            return response()->json(['message' => 'Supplier not found'], 404);
        }

        $supplier->delete();

        return response()->json(['message' => 'Supplier deleted successfully'], 200);
    }

    public function show($id)
    {
        $produk = Produk::with(['bahan', 'sizes.satuan'])->findOrFail($id);

        if ($produk->ukuran) {
            // Standard size
            $size = Size::find($produk->ukuran);
            $price = $produk->HargaProduk;
        } else {
            // Custom size
            $size = 'Custom';
            $price = $produk->custom_harga;
        }

        return view('admin.showproduk', compact('produk', 'size', 'price'));
    }
    public function sizes()
    {
        return $this->belongsToMany(Size::class, 'produk_size', 'IdProduk', 'id_ukuran')
                    ->withPivot('harga')
                    ->withTimestamps();
    }
}