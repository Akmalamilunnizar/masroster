<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Size;
// use App\Models\Satuan; // removed
use Illuminate\Http\Request;

class SizeController extends Controller
{
    public function index() {
        $sizes = Size::all();
        return view('admin.allukuran', compact('sizes'));
    }

    public function create() {
        // Satuan removed; view should not require it
        return view('admin.addukuran');
    }

    public function store(Request $request) {
        $request->validate([
            'nama' => 'required|string|max:50',
            'panjang' => 'required|integer',
            'lebar' => 'required|integer',
            // 'id_satuan' => 'required|string|exists:satuan,IdSatuan',
        ]);

        Size::create($request->all());
        return redirect()->route('allukuran')->with('message', 'Ukuran berhasil ditambahkan!');
    }

    public function edit($id) {
        $size = Size::findOrFail($id);
        return view('admin.editukuran', compact('size'));
    }

    public function update(Request $request, $id) {
        $request->validate([
            'nama' => 'required|string|max:50',
            'panjang' => 'required|integer',
            'lebar' => 'required|integer',
            // 'id_satuan' => 'required|string|exists:satuan,IdSatuan',
        ]);

        $size = Size::findOrFail($id);
        $size->update($request->all());
        
        return redirect()->route('allukuran')->with('message', 'Ukuran berhasil diperbarui!');
    }

    public function destroy($id) {
        $size = Size::findOrFail($id);
        $size->delete();
        
        return redirect()->route('allukuran')->with('message', 'Ukuran berhasil dihapus!');
    }

    public function quickAddSize(Request $request)
    {
        try {
            $request->validate([
                'nama' => 'required|string|max:50',
                'panjang' => 'required|integer',
                'lebar' => 'required|integer'
            ]);

            $size = Size::create([
                'nama' => $request->nama,
                'panjang' => $request->panjang,
                'lebar' => $request->lebar
            ]);

            return response()->json([
                'success' => true,
                'id' => $size->id_ukuran,
                'name' => $size->nama,
                'panjang' => $size->panjang,
                'lebar' => $size->lebar
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
