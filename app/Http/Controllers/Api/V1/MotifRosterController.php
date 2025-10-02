<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MotifRoster;
use App\Models\DetailMotif;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MotifRosterController extends Controller
{
    public function index()
    {
        $motifs = MotifRoster::with(['tipeRosters'])->orderBy('IdMotif', 'asc')->get();
        return view('admin.allmotif', compact('motifs'));
    }

    public function create()
    {
        $tipeList = \App\Models\TipeRoster::all();
        $motifList = MotifRoster::all();
        return view('admin.addmotif', compact('tipeList', 'motifList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_motif' => 'required|string|max:35|unique:motif_roster,nama_motif',
        ]);

        MotifRoster::create([
            'nama_motif' => $request->nama_motif,
        ]);

        return redirect()->route('addmotif')->with('message', 'Motif berhasil ditambahkan!');
    }

    public function storeDetailMotif(Request $request)
    {
        $request->validate([
            'id_tipe' => 'required|exists:tipe_roster,IdTipe',
            'id_motif' => 'required|exists:motif_roster,IdMotif',
        ]);

        // Check if connection already exists
        $existing = DetailMotif::where('id_tipe', $request->id_tipe)
            ->where('id_motif', $request->id_motif)
            ->first();

        if ($existing) {
            return redirect()->route('addmotif')->with('error', 'Hubungan tipe dan motif ini sudah ada!');
        }

        // Create the connection
        DetailMotif::create([
            'id_tipe' => $request->id_tipe,
            'id_motif' => $request->id_motif,
        ]);

        return redirect()->route('addmotif')->with('message', 'Hubungan tipe dan motif berhasil dibuat!');
    }

    public function edit($id)
    {
        $motif = MotifRoster::with('tipeRosters')->findOrFail($id);
        $tipeList = \App\Models\TipeRoster::all();
        return view('admin.editmotif', compact('motif', 'tipeList'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_motif' => 'required|string|max:35|unique:motif_roster,nama_motif,' . $id . ',IdMotif',
            'tipe_connections' => 'nullable|array',
            'tipe_connections.*' => 'exists:tipe_roster,IdTipe',
        ]);

        $motif = MotifRoster::findOrFail($id);
        
        // Update motif name
        $motif->update(['nama_motif' => $request->nama_motif]);
        
        // Update connections
        $tipeConnections = $request->tipe_connections ?? [];
        
        // Get current connections
        $currentConnections = $motif->tipeRosters->pluck('IdTipe')->toArray();
        
        // Remove old connections that are no longer selected
        $connectionsToRemove = array_diff($currentConnections, $tipeConnections);
        if (!empty($connectionsToRemove)) {
            DetailMotif::where('id_motif', $id)
                ->whereIn('id_tipe', $connectionsToRemove)
                ->delete();
        }
        
        // Add new connections
        $connectionsToAdd = array_diff($tipeConnections, $currentConnections);
        foreach ($connectionsToAdd as $tipeId) {
            DetailMotif::create([
                'id_tipe' => $tipeId,
                'id_motif' => $id,
            ]);
        }
        
        return redirect()->route('allmotif')->with('message', 'Motif berhasil diperbarui!');
    }

    public function destroy($id)
    {
        MotifRoster::findOrFail($id)->delete();
        return redirect()->route('allmotif')->with('message', 'Motif berhasil dihapus!');
    }

    public function batchDelete(Request $request)
    {
        $request->validate([
            'motif_ids' => 'required|array',
            'motif_ids.*' => 'required|integer'
        ]);

        $ids = $request->motif_ids;
        $deleted = MotifRoster::whereIn('IdMotif', $ids)->delete();

        return redirect()->route('allmotif')->with('message', "Berhasil menghapus $deleted motif!");
    }

    public function quickAddMotif(Request $request)
    {
        try {
            $request->validate([
                'nama_motif' => 'required|string|max:35|unique:motif_roster,nama_motif',
                'id_tipe' => 'required|exists:tipe_roster,IdTipe'
            ]);

            DB::beginTransaction();
            
            // Create the motif
            $motif = MotifRoster::create([
                'nama_motif' => $request->nama_motif
            ]);

            // Create the connection in detail_motif
            DetailMotif::create([
                'id_tipe' => $request->id_tipe,
                'id_motif' => $motif->IdMotif
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'id' => $motif->IdMotif,
                'name' => $motif->nama_motif
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


