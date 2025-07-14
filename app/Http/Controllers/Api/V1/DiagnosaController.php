<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\DiagnosaPenyakit;
use Illuminate\Http\Request;

class DiagnosaController extends Controller
{
    public function index()
    {
        try {
            $diagnoses_d = DiagnosaPenyakit::with(['koiFish', 'penyakit'])->get();
            $diagnosas = DiagnosaPenyakit::all();
        } catch (\Exception $e) {
            // Kalau error (misal tabel gak ada), isi array kosong aja
            $diagnoses_d = collect();
            $diagnosas = collect();

            // Atau kalau mau debugging errornya
            // dd($e->getMessage());
        }

        return view("admin.allDiagnosaPenyakit", compact('diagnosas', 'diagnoses_d'));
    }
    // Menampilkan semua diagnosa penyakit
    // public function index()
    // {
    //     $diagnoses_d = DiagnosaPenyakit::with(['koiFish', 'penyakit'])->get();
    //     $diagnosas = DiagnosaPenyakit::all();

    //     return view("admin.allDiagnosaPenyakit", compact('diagnosas', 'diagnoses_d'));
    // }

    // Pencarian diagnosa penyakit berdasarkan jenis koi atau penyakit
    public function searchDiagnosa(Request $request)
    {
        $search = $request->search;

        $diagnosas = DiagnosaPenyakit::where('jenis_koi', 'like', "%$search%")
            ->orWhere('penyakit', 'like', "%$search%")
            ->get();

        return view('admin.allDiagnosaPenyakit', compact('diagnosas', 'search'));
    }

    // Menampilkan form untuk menambah diagnosa baru
    public function addDiagnosa()
    {
        return view('admin.addDiagnosa');
    }

    // Menyimpan diagnosa baru ke dalam database
    public function storeDiagnosa(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'jenis_koi' => 'required|string',
            'penyakit' => 'required|string',
            'penyebab' => 'required|string',
            'gambar_koi' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'keterangan' => 'nullable|string',
        ]);

        // Menyimpan gambar jika ada
        $gambarKoiPath = null;
        if ($request->hasFile('gambar_koi')) {
            $gambarKoiPath = $request->file('gambar_koi')->store('images/koi', 'public');
        }

        DiagnosaPenyakit::create([
            'tanggal' => $request->tanggal,
            'jenis_koi' => $request->jenis_koi,
            'penyakit' => $request->penyakit,
            'penyebab' => $request->penyebab,
            'gambar_koi' => $gambarKoiPath,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('allDiagnosaPenyakit')->with('message', 'Diagnosa penyakit berhasil ditambah!');
    }

    // Menampilkan form edit diagnosa penyakit berdasarkan ID
    public function editDiagnosa($id)
    {
        $diagnosa = DiagnosaPenyakit::findOrFail($id);

        return view('admin.editdiagnosa', compact('diagnosa'));
    }

    // Memperbarui diagnosa penyakit
    public function updateDiagnosa(Request $request, $id)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'jenis_koi' => 'required|string',
            'penyakit' => 'required|string',
            'penyebab' => 'required|string',
            'gambar_koi' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'keterangan' => 'nullable|string',
        ]);

        $diagnosa = DiagnosaPenyakit::findOrFail($id);

        // Menyimpan gambar baru jika diunggah
        if ($request->hasFile('gambar_koi')) {
            $gambarKoiPath = $request->file('gambar_koi')->store('images/koi', 'public');
            $diagnosa->gambar_koi = $gambarKoiPath;
        }

        $diagnosa->update([
            'tanggal' => $request->tanggal,
            'jenis_koi' => $request->jenis_koi,
            'penyakit' => $request->penyakit,
            'penyebab' => $request->penyebab,
            'keterangan' => $request->keterangan,
        ]);


        return redirect()->route('allDiagnosaPenyakit')->with('message', 'Diagnosa penyakit berhasil diperbarui!');

    }

    // Menghapus diagnosa penyakit
    public function deleteDiagnosa($id)
    {
        $diagnosa = DiagnosaPenyakit::findOrFail($id);
        $diagnosa->delete();

        return redirect()->route('allDiagnosaPenyakit')->with('message', 'Diagnosa penyakit berhasil dihapus');
    }

    public function showDiagnosa($id)
    {
        $diagnoses_d = DiagnosaPenyakit::with(['koiFish', 'penyakit'])->get();
        $diagnosa = DiagnosaPenyakit::findOrFail($id);
        return view('admin.detailDiagnosaPenyakit', compact('diagnosa', 'diagnoses_d'));
    }

}
