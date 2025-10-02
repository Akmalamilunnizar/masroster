<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\DetailMasuk;
use App\Models\TypeItems;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::suppliers()->get();
        return view('admin.allsupplier', compact('suppliers'));
    }

    public function searchSupplier(Request $request)
    {
        $search = $request->search;

        $suppliers = Supplier::suppliers()->where(function ($query) use ($search) {
            $query->where('id', 'like', "%$search%")
                  ->orWhere('f_name', 'like', "%$search%")
                  ->orWhere('nomor_telepon', 'like', "%$search%")
                  ->orWhere('alamat', 'like', "%$search%");
        })->get();

        return view('admin.allsupplier', compact('suppliers', 'search'));
    }

    public function addSupplier()
    {
        $lastMasuk = DetailMasuk::orderBy('IdMasuk', 'desc')->first();
        $newIdMasuk = $lastMasuk ? 'BM' . str_pad((int) substr($lastMasuk->IdMasuk, 2) + 1, 4, '0', STR_PAD_LEFT) : 'BM0001';

        // Ambil ID terakhir dari tabel users dengan role "User"
        $lastSupplier = Supplier::suppliers()->orderBy('id', 'desc')->first();
        $newIdSupplier = $lastSupplier ? 'SP' . str_pad($lastSupplier->id + 1, 4, '0', STR_PAD_LEFT) : 'SP0001';

        $suppliers = Supplier::suppliers()->get();
        $typeid = TypeItems::all();

        return view("admin.additems", compact('typeid', 'newIdSupplier', 'newIdMasuk', 'typeid', 'suppliers'));
    }

    public function storeSupplier(Request $request)
    {
        $request->validate([
            'IdSupplier' => 'required|unique:users,id',
            'NamaSupplier' => 'required',
            'NoTelp' => 'required',
        ]);

        // Extract numeric ID from IdSupplier (e.g., "SP0001" -> 1)
        $numericId = (int) substr($request->IdSupplier, 2);

        Supplier::create([
            'id' => $numericId,
            'f_name' => $request->NamaSupplier,
            'nomor_telepon' => $request->NoTelp,
            'email' => $request->NamaSupplier . '@supplier.com', // Generate email
            'username' => strtolower(str_replace(' ', '', $request->NamaSupplier)), // Generate username
            'password' => bcrypt('password123'), // Default password
            'user' => 'User', // Set role as User
            'img' => 'default-avatar.png'
        ]);

        return redirect()->route('allsuppliers')->with('message', 'Supplier berhasil ditambahkan!');
    }

    public function editSupplier($IdSupplier)
    {
        // Extract numeric ID from IdSupplier
        $numericId = (int) substr($IdSupplier, 2);
        $supplier = Supplier::suppliers()->findOrFail($numericId);
        return view('admin.editsupplier', compact('supplier'));
    }

    public function updateSupplier(Request $request, $IdSupplier)
    {
        // Extract numeric ID from IdSupplier
        $numericId = (int) substr($IdSupplier, 2);
        $supplier = Supplier::suppliers()->findOrFail($numericId);

        $request->validate([
            'NamaSupplier' => 'required',
            'NoTelp' => 'required',
        ]);

        $supplier->update([
            'f_name' => $request->NamaSupplier,
            'nomor_telepon' => $request->NoTelp,
        ]);

        return redirect()->route('allsuppliers')->with('message', 'Data supplier berhasil diperbarui!');
    }

    public function deleteSupplier($IdSupplier)
    {
        // Extract numeric ID from IdSupplier
        $numericId = (int) substr($IdSupplier, 2);
        Supplier::suppliers()->findOrFail($numericId)->delete();
        return redirect()->route('allsuppliers')->with('message', 'Supplier berhasil dihapus!');
    }

    public function get_supplier_list()
    {
        $supplier = Supplier::suppliers()->get();
        return response()->json($supplier, 200);
    }

    public function destroy($id)
    {
        $supplier = Supplier::suppliers()->find($id);

        if (!$supplier) {
            return redirect()->back()->with('error', 'Supplier tidak ditemukan.');
        }

        $supplier->delete();
        return redirect()->back()->with('message', 'Supplier berhasil dihapus.');
    }
}
