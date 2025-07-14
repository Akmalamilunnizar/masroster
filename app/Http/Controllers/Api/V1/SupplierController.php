<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::all();
        return view('admin.allsupplier', compact('suppliers'));
    }

    public function searchSupplier(Request $request)
    {
        $search = $request->search;

        $suppliers = Supplier::where(function ($query) use ($search) {
            $query->where('IdSupplier', 'like', "%$search%")
                  ->orWhere('NamaSupplier', 'like', "%$search%")
                  ->orWhere('NoTelp', 'like', "%$search%")
                  ->orWhere('Alamat', 'like', "%$search%");
        })->get();

        return view('admin.allsupplier', compact('suppliers', 'search'));
    }

    public function addSupplier()
    {
        $lastMasuk = DetailMasuk::orderBy('IdMasuk', 'desc')->first();
        $newIdMasuk = $lastMasuk ? 'BM' . str_pad((int) substr($lastMasuk->IdMasuk, 2) + 1, 4, '0', STR_PAD_LEFT) : 'BM0001';
        // dd($username);

        // Ambil ID terakhir dari tabel supplier
        $lastSupplier = Supplier::orderBy('IdSupplier', 'desc')->first();
        $newIdSupplier = $lastSupplier ? 'SP' . str_pad((int) substr($lastSupplier->IdSupplier, 2) + 1, 4, '0', STR_PAD_LEFT) : 'SP0001';

        $suppliers = Supplier::all();
        $typeid = TypeItems::all();
        $typeS = Satuan::all();

        return view("admin.additems", compact('typeid', 'typeS', 'newIdSupplier', 'newIdMasuk', 'typeid', 'typeS', 'suppliers', 'username'));
        return view('admin.addsupplier');
    }

    public function storeSupplier(Request $request)
    {
        $request->validate([
            'IdSupplier' => 'required|unique:supplier',
            'NamaSupplier' => 'required',
            'NoTelp' => 'required',
            'Alamat' => 'required',
        ]);

        Supplier::create($request->only(['IdSupplier', 'NamaSupplier', 'NoTelp', 'Alamat']));

        return redirect()->route('allsuppliers')->with('message', 'Supplier berhasil ditambahkan!');
    }


    public function editSupplier($IdSupplier)
    {
        $supplier = Supplier::findOrFail($IdSupplier);
        return view('admin.editsupplier', compact('supplier'));
    }

    public function updateSupplier(Request $request, $IdSupplier)
    {
        $supplier = Supplier::findOrFail($IdSupplier);

        $request->validate([
            'NamaSupplier' => 'required',
            'NoTelp' => 'required',
            'Alamat' => 'required',
        ]);

        $supplier->update($request->all());

        return redirect()->route('allsuppliers')->with('message', 'Data supplier berhasil diperbarui!');
    }

    public function deleteSupplier($IdSupplier)
    {
        Supplier::findOrFail($IdSupplier)->delete();
        return redirect()->route('allsuppliers')->with('message', 'Supplier berhasil dihapus!');
    }

    public function get_supplier_list()
    {
        $supplier = Supplier::all();
        return response()->json($supplier, 200);
    }

    public function destroy($id)
    {
        $supplier = Supplier::find($id);

        if (!$supplier) {
            return redirect()->back()->with('error', 'Supplier tidak ditemukan.');
        }


        $supplier->delete();
        return redirect()->back()->with('message', 'Supplier berhasil dihapus.');

    }

}
