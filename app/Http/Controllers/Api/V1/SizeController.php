<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Size;
use App\Models\Satuan;
use Illuminate\Http\Request;

class SizeController extends Controller
{
    public function index() {
        $sizes = Size::with('satuan')->get();
        return view('admin.allukuran', compact('sizes'));
    }

    public function create() {
        $satuanList = Satuan::all();
        return view('admin.addukuran', compact('satuanList'));
    }

    public function store(Request $request) {
        $request->validate([
            'nama' => 'required|string|max:50',
            'panjang' => 'required|integer',
            'lebar' => 'required|integer',
            'id_satuan' => 'required|string|exists:satuan,IdSatuan',
        ]);

        Size::create($request->all());
        return redirect()->route('allukuran')->with('message', 'Ukuran berhasil ditambahkan!');
    }

    public function edit($id) {
        $size = Size::findOrFail($id);
        $satuanList = Satuan::all();
        return view('admin.editukuran', compact('size', 'satuanList'));
    }

    public function update(Request $request, $id) {
        $request->validate([
            'nama' => 'required|string|max:50',
            'panjang' => 'required|integer',
            'lebar' => 'required|integer',
            'id_satuan' => 'required|string|exists:satuan,IdSatuan',
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
}
