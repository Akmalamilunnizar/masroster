<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DetailHarga;
use App\Models\Produk;
use App\Models\User;
use App\Models\Size;
use App\Models\TypeItems;
use App\Models\MotifRoster;
use Illuminate\Support\Facades\DB;

class DetailHargaController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));

        // Base query keeps eager loads for view accessors/relations
        $detailHargaQuery = DetailHarga::query()->with(['roster.jenisRoster', 'roster.motif', 'user', 'size']);

        if ($search !== '') {
            $collation = 'utf8mb4_unicode_ci';

            // Use explicit joins with consistent collation to avoid mixed-collation errors
            $detailHargaQuery
                ->leftJoin('produk', function ($join) use ($collation) {
                    $join->on(DB::raw("detail_harga.id_roster COLLATE $collation"), '=', DB::raw("produk.IdRoster COLLATE $collation"));
                })
                ->leftJoin('users', function ($join) use ($collation) {
                    $join->on(DB::raw("detail_harga.id_user COLLATE $collation"), '=', DB::raw("users.id COLLATE $collation"));
                })
                ->leftJoin('size', 'detail_harga.id_ukuran', '=', 'size.id_ukuran')
                ->leftJoin('jenisbarang', 'produk.IdJenisBarang', '=', 'jenisbarang.IdJenisBarang')
                ->leftJoin('motif_roster', 'produk.id_motif', '=', 'motif_roster.IdMotif')
                ->where(function ($q) use ($search, $collation) {
                    $q->orWhere(DB::raw("detail_harga.id_roster COLLATE $collation"), 'LIKE', "%$search%")
                      ->orWhere(DB::raw("detail_harga.id_user COLLATE $collation"), 'LIKE', "%$search%")
                      ->orWhere(DB::raw("detail_harga.id_ukuran COLLATE $collation"), 'LIKE', "%$search%")
                      ->orWhere(DB::raw("produk.NamaRoster COLLATE $collation"), 'LIKE', "%$search%")
                      ->orWhere(DB::raw("produk.IdRoster COLLATE $collation"), 'LIKE', "%$search%")
                      ->orWhere(DB::raw("jenisbarang.JenisBarang COLLATE $collation"), 'LIKE', "%$search%")
                      ->orWhere(DB::raw("motif_roster.nama_motif COLLATE $collation"), 'LIKE', "%$search%")
                      ->orWhere(DB::raw("users.f_name COLLATE $collation"), 'LIKE', "%$search%")
                      ->orWhere(DB::raw("users.id COLLATE $collation"), 'LIKE', "%$search%")
                      ->orWhere(DB::raw("size.nama COLLATE $collation"), 'LIKE', "%$search%");
                })
                ->select('detail_harga.*');
        }

        $detailHarga = $detailHargaQuery->orderBy('detail_harga.id_roster')->get();

        return view('admin.detailharga.index', compact('detailHarga'));
    }

    public function create()
    {
        $rosters = Produk::with(['jenisRoster', 'motif'])->get();
        $users = User::where('user', 'User')->get(); 
        $sizes = Size::all();
        $jenisList = TypeItems::all();
        $motifList = MotifRoster::all();

        return view('admin.detailharga.create', compact('rosters', 'users', 'sizes', 'jenisList', 'motifList'));
    }

    public function store(Request $request)
    {
        // Clean up formatted values before validation
        $requestData = $request->all();
        
        // Convert formatted harga to raw number
        if (isset($requestData['harga'])) {
            $requestData['harga'] = (int) str_replace(['.', ','], '', $requestData['harga']);
        }
        
        // Update the request with cleaned values
        $request->merge($requestData);
        
        $request->validate([
            'id_roster' => 'required|exists:produk,IdRoster',
            'id_user' => 'required|exists:users,id',
            'id_ukuran' => 'required|exists:size,id_ukuran',
            'harga' => 'required|integer|min:0',
        ]);

        try {
            // Check if combination already exists
            $existing = DetailHarga::where('id_roster', $request->id_roster)
                ->where('id_user', $request->id_user)
                ->where('id_ukuran', $request->id_ukuran)
                ->first();

            if ($existing) {
                // Update existing record
                $existing->update(['harga' => $request->harga]);
                $message = 'Harga berhasil diperbarui!';
            } else {
                // Create new record
                DetailHarga::create([
                    'id_roster' => $request->id_roster,
                    'id_user' => $request->id_user,
                    'id_ukuran' => $request->id_ukuran,
                    'harga' => $request->harga,
                ]);
                $message = 'Harga berhasil ditambahkan!';
            }

            return redirect()->route('detailharga.index')->with('message', $message);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit($id_roster, $id_user, $id_ukuran)
    {
        $detailHarga = DetailHarga::where('id_roster', $id_roster)
            ->where('id_user', $id_user)
            ->where('id_ukuran', $id_ukuran)
            ->firstOrFail();

        $rosters = Produk::with(['jenisRoster', 'motif'])->get();
        $users = User::where('user', 'User')->get();
        $sizes = Size::all();

        return view('admin.detailharga.edit', compact('detailHarga', 'rosters', 'users', 'sizes'));
    }

    public function update(Request $request, $id_roster, $id_user, $id_ukuran)
    {
        // Clean up formatted values before validation
        $requestData = $request->all();
        
        // Convert formatted harga to raw number
        if (isset($requestData['harga'])) {
            $requestData['harga'] = (int) str_replace(['.', ','], '', $requestData['harga']);
        }
        
        // Update the request with cleaned values
        $request->merge($requestData);
        
        $request->validate([
            'harga' => 'required|integer|min:0',
        ]);

        try {
            // Use DB query builder to avoid Eloquent primary key issues
            $updated = DB::table('detail_harga')
                ->where('id_roster', $id_roster)
                ->where('id_user', $id_user)
                ->where('id_ukuran', $id_ukuran)
                ->update(['harga' => $request->harga]);

            if ($updated) {
                return redirect()->route('detailharga.index')->with('message', 'Harga berhasil diperbarui!');
            } else {
                return redirect()->back()->with('error', 'Record tidak ditemukan');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($id_roster, $id_user, $id_ukuran)
    {
        try {
            DetailHarga::where('id_roster', $id_roster)
                ->where('id_user', $id_user)
                ->where('id_ukuran', $id_ukuran)
                ->delete();

            return redirect()->route('detailharga.index')->with('message', 'Harga berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // Get roster prices by type, size, and motif
    public function getRosterPrices(Request $request)
    {
        $jenisId = $request->jenis_id;
        $motifId = $request->motif_id;
        $sizeId = $request->size_id;

        $rosters = Produk::with(['jenisRoster', 'motif', 'sizes'])
            ->where('IdJenisBarang', $jenisId)
            ->when($motifId, function($query) use ($motifId) {
                return $query->where('id_motif', $motifId);
            })
            ->get();

        return response()->json($rosters);
    }

    // Get user last prices
    public function getUserLastPrices(Request $request)
    {
        $userId = $request->user_id;
        
        $lastPrices = DetailHarga::with(['roster.jenisRoster', 'roster.motif', 'user'])
            ->where('id_user', $userId)
            ->get();

        return response()->json($lastPrices);
    }

    // Batch delete functionality
    public function batchDelete(Request $request)
    {
        $request->validate([
            'harga_ids' => 'required|array',
            'harga_ids.*' => 'required|string'
        ]);

        $deletedCount = 0;
        $errors = [];

        foreach ($request->harga_ids as $hargaId) {
            try {
                // Split the combined ID to get roster, user, and ukuran IDs
                $parts = explode('_', $hargaId);
                if (count($parts) !== 3) {
                    $errors[] = "Invalid ID format: $hargaId";
                    continue;
                }

                $idRoster = $parts[0];
                $idUser = $parts[1];
                $idUkuran = $parts[2];

                // Delete the detail harga record
                $deleted = DetailHarga::where('id_roster', $idRoster)
                    ->where('id_user', $idUser)
                    ->where('id_ukuran', $idUkuran)
                    ->delete();

                if ($deleted) {
                    $deletedCount++;
                } else {
                    $errors[] = "Record not found for roster: $idRoster, user: $idUser, ukuran: $idUkuran";
                }
            } catch (\Exception $e) {
                $errors[] = "Gagal menghapus record dengan ID: $hargaId - " . $e->getMessage();
            }
        }

        if (count($errors) > 0) {
            return redirect()->route('detailharga.index')->with('error', 'Beberapa record gagal dihapus: ' . implode(', ', $errors));
        }

        return redirect()->route('detailharga.index')->with('message', "Berhasil menghapus $deletedCount record harga!");
    }
}
