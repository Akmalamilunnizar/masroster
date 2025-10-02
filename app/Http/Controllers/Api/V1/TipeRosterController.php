<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TipeRoster;
use App\Models\DetailTipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TipeRosterController extends Controller
{
    public function index()
    {
        $tipeRosters = TipeRoster::with(['jenisRosters'])->orderBy('IdTipe', 'asc')->get();
        return view('admin.alltiperoster', compact('tipeRosters'));
    }

    public function create()
    {
        $jenisList = \App\Models\TypeItems::all();
        $tipeList = TipeRoster::all();
        return view('admin.addtiperoster', compact('jenisList', 'tipeList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'namaTipe' => 'required|string|max:40|unique:tipe_roster,namaTipe',
        ]);

        TipeRoster::create([
            'namaTipe' => $request->namaTipe,
        ]);

        return redirect()->route('addtiperoster')->with('message', 'Tipe Roster berhasil ditambahkan!');
    }

    public function storeDetailTipe(Request $request)
    {
        $request->validate([
            'id_jenis' => 'required|exists:jenisbarang,IdJenisBarang',
            'id_tipe' => 'required|exists:tipe_roster,IdTipe',
        ]);

        // Check if connection already exists
        $existing = DetailTipe::where('id_jenis', $request->id_jenis)
            ->where('id_tipe', $request->id_tipe)
            ->first();

        if ($existing) {
            return redirect()->route('addtiperoster')->with('error', 'Hubungan jenis dan tipe ini sudah ada!');
        }

        // Create the connection
        DetailTipe::create([
            'id_jenis' => $request->id_jenis,
            'id_tipe' => $request->id_tipe,
        ]);

        return redirect()->route('addtiperoster')->with('message', 'Hubungan jenis dan tipe berhasil dibuat!');
    }

    public function edit($id)
    {
        $tipeRoster = TipeRoster::with('jenisRosters')->findOrFail($id);
        $jenisList = \App\Models\TypeItems::all();
        return view('admin.edittiperoster', compact('tipeRoster', 'jenisList'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'namaTipe' => 'required|string|max:40|unique:tipe_roster,namaTipe,' . $id . ',IdTipe',
            'jenis_connections' => 'nullable|array',
            'jenis_connections.*' => 'exists:jenisbarang,IdJenisBarang',
        ]);
        
        $tipeRoster = TipeRoster::findOrFail($id);
        
        // Update tipe name
        $tipeRoster->update(['namaTipe' => $request->namaTipe]);
        
        // Update connections
        $jenisConnections = $request->jenis_connections ?? [];
        
        // Get current connections
        $currentConnections = $tipeRoster->jenisRosters->pluck('IdJenisBarang')->toArray();
        
        // Remove old connections that are no longer selected
        $connectionsToRemove = array_diff($currentConnections, $jenisConnections);
        if (!empty($connectionsToRemove)) {
            DetailTipe::where('id_tipe', $id)
                ->whereIn('id_jenis', $connectionsToRemove)
                ->delete();
        }
        
        // Add new connections
        $connectionsToAdd = array_diff($jenisConnections, $currentConnections);
        foreach ($connectionsToAdd as $jenisId) {
            DetailTipe::create([
                'id_jenis' => $jenisId,
                'id_tipe' => $id,
            ]);
        }
        
        return redirect()->route('alltiperoster')->with('message', 'Tipe Roster berhasil diperbarui!');
    }

    public function destroy($id)
    {
        TipeRoster::findOrFail($id)->delete();
        return redirect()->route('alltiperoster')->with('message', 'Tipe Roster berhasil dihapus!');
    }

    public function batchDelete(Request $request)
    {
        $request->validate([
            'tipe_ids' => 'required|array',
            'tipe_ids.*' => 'required|integer'
        ]);

        $ids = $request->tipe_ids;
        $deleted = TipeRoster::whereIn('IdTipe', $ids)->delete();

        return redirect()->route('alltiperoster')->with('message', "Berhasil menghapus $deleted tipe roster!");
    }

    public function quickAddTipe(Request $request)
    {
        try {
            $request->validate([
                'namaTipe' => 'required|string|max:40|unique:tipe_roster,namaTipe',
                'id_jenis' => 'required|exists:jenisbarang,IdJenisBarang'
            ]);

            DB::beginTransaction();
            
            // Create the tipe
            $tipe = TipeRoster::create([
                'namaTipe' => $request->namaTipe
            ]);

            // Create the connection in detail_tipe
            DetailTipe::create([
                'id_jenis' => $request->id_jenis,
                'id_tipe' => $tipe->IdTipe
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'id' => $tipe->IdTipe,
                'name' => $tipe->namaTipe
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
