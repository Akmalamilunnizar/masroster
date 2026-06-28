<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DetailHarga;
use App\Models\MotifRoster;
use App\Models\Produk;
use App\Models\Size;
use App\Models\TypeItems;
use App\Models\User;
use Illuminate\Http\Request;
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
        $data = $request->only(['id_roster', 'id_user', 'id_ukuran', 'harga']);
        if (isset($data['harga'])) {
            $data['harga'] = (int) str_replace(['.', ','], '', (string) $data['harga']);
        }

        $validated = validator($data, [
            'id_roster' => 'required|exists:produk,IdRoster',
            'id_user' => 'required|exists:users,id',
            'id_ukuran' => 'required|exists:size,id_ukuran',
            'harga' => 'required|integer|min:0',
        ])->validate();

        try {
            // Check if combination already exists
            $existing = DetailHarga::where('id_roster', $validated['id_roster'])
                ->where('id_user', $validated['id_user'])
                ->where('id_ukuran', $validated['id_ukuran'])
                ->first();

            if ($existing) {
                // Update existing record
                $existing->update(['harga' => $validated['harga']]);
                $message = 'Harga berhasil diperbarui!';
            } else {
                // Create new record
                DetailHarga::create([
                    'id_roster' => $validated['id_roster'],
                    'id_user' => $validated['id_user'],
                    'id_ukuran' => $validated['id_ukuran'],
                    'harga' => $validated['harga'],
                ]);
                $message = 'Harga berhasil ditambahkan!';
            }

            return redirect()->route('detailharga.index')->with('message', $message);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
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
        $data = $request->only(['harga']);
        if (isset($data['harga'])) {
            $data['harga'] = (int) str_replace(['.', ','], '', (string) $data['harga']);
        }

        $validated = validator($data, [
            'harga' => 'required|integer|min:0',
        ])->validate();

        try {
            // Use DB query builder to avoid Eloquent primary key issues
            $updated = DB::table('detail_harga')
                ->where('id_roster', $id_roster)
                ->where('id_user', $id_user)
                ->where('id_ukuran', $id_ukuran)
                ->update(['harga' => $validated['harga']]);

            if ($updated) {
                return redirect()->route('detailharga.index')->with('message', 'Harga berhasil diperbarui!');
            } else {
                return redirect()->back()->with('error', 'Record tidak ditemukan');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
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
            return redirect()->back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    // Get roster prices by type, size, and motif
    public function getRosterPrices(Request $request)
    {
        $validated = $request->validate([
            'jenis_id' => 'required|integer|exists:jenisbarang,IdJenisBarang',
            'motif_id' => 'nullable|integer|exists:motif_roster,IdMotif',
            'size_id' => 'nullable|integer|exists:size,id_ukuran',
        ]);

        $jenisId = (int) $validated['jenis_id'];
        $motifId = $validated['motif_id'] ?? null;
        $sizeId = $validated['size_id'] ?? null;

        $rosters = Produk::with(['jenisRoster', 'motif', 'sizes'])
            ->where('id_jenis', $jenisId)
            ->when($motifId, function ($query) use ($motifId) {
                return $query->where('id_motif', $motifId);
            })
            ->when($sizeId, function ($query) use ($sizeId) {
                return $query->where(function ($sizeQuery) use ($sizeId) {
                    $sizeQuery->whereHas('sizes', function ($pivotQuery) use ($sizeId) {
                        $pivotQuery->where('size.id_ukuran', $sizeId);
                    })->orWhereExists(function ($legacyPivotQuery) use ($sizeId) {
                        $legacyPivotQuery->select(DB::raw(1))
                            ->from('produk_size')
                            ->whereColumn('produk_size.IdRoster', 'produk.IdRoster')
                            ->where('produk_size.id_ukuran', $sizeId);
                    });
                });
            })
            ->get();

        return response()->json($rosters);
    }

    // Get user last prices
    public function getUserLastPrices(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $lastPrices = DetailHarga::with(['roster.jenisRoster', 'roster.motif', 'user'])
            ->where('id_user', $validated['user_id'])
            ->get();

        return response()->json($lastPrices);
    }

    // Batch delete functionality
    public function batchDelete(Request $request)
    {
        $request->validate([
            'harga_ids' => 'required|array',
            'harga_ids.*' => ['required', 'string', 'regex:/^[^_]+_[^_]+_[^_]+$/'],
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
                $errors[] = "Gagal menghapus record dengan ID: $hargaId - ".$e->getMessage();
            }
        }

        if (count($errors) > 0) {
            return redirect()->route('detailharga.index')->with('error', 'Beberapa record gagal dihapus: '.implode(', ', $errors));
        }

        return redirect()->route('detailharga.index')->with('message', "Berhasil menghapus $deletedCount record harga!");
    }
}
