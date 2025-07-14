<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DetailMasuk;
use App\Models\BarangMasuk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\TypeItems;
use App\Models\Satuan;
use App\Models\Supplier;
use App\Models\Items;
use Illuminate\Validation\Rule;
use Encore\Admin\Layout\Content;
use Barryvdh\DomPDF\Facade\Pdf;

class ItemsController extends Controller
{
    public function Index(Request $request)
    {
        $bulan = $request->query('bulan');
        $tahun = $request->query('tahun');
        $search = $request->input('search');
        $jenis_barang = $request->input('jenis_barang');

        // Get all jenis barang for the filter dropdown
        $jenisBarang = TypeItems::all();

        // Start query with relationships
        $query = Items::with(['jenisBarang', 'satuan']);

        // Filter by jenis barang if selected
        if ($jenis_barang) {
            $query->where('IdJenisBarang', $jenis_barang);
        }

        // Filter by search term
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('IdBarang', 'like', "%{$search}%")
                  ->orWhere('NamaBarang', 'like', "%{$search}%");
            });
        }

        // Get all items
        $items = $query->get();

        foreach ($items as $item) {
            // Try to get the latest stock-in for the selected month/year
            $queryMasuk = DetailMasuk::where('IdBarang', $item->IdBarang)
                ->when($bulan, function ($q) use ($bulan) {
                    $q->whereMonth('created_at', $bulan);
                })
                ->when($tahun, function ($q) use ($tahun) {
                    $q->whereYear('created_at', $tahun);
                })
                ->orderBy('created_at', 'desc')
                ->first();

            // If not found, get the latest stock-in regardless of filter
            if (!$queryMasuk) {
                $queryMasuk = DetailMasuk::where('IdBarang', $item->IdBarang)
                    ->orderBy('created_at', 'desc')
                    ->first();
            }

            $item->latestDetailMasuk = $queryMasuk;

            if ($item->latestDetailMasuk) {
                $item->latestDetailMasuk->load('supplier');
            }

            $item->latestDetailKeluar = DB::table('detail_barangkeluar')
                ->where('IdBarang', $item->IdBarang)
                ->orderBy('created_at', 'desc')
                ->first();
        }

        // Get stock history with filters
        $riwayatStok = DB::table('detail_barangmasuk')
            ->join('databarang', 'detail_barangmasuk.IdBarang', '=', 'databarang.IdBarang')
            ->join('barangmasuk', 'detail_barangmasuk.IdMasuk', '=', 'barangmasuk.IdMasuk')
            ->when($bulan, function ($q) use ($bulan) {
                $q->whereMonth('barangmasuk.tglMasuk', $bulan);
            })
            ->when($tahun, function ($q) use ($tahun) {
                $q->whereYear('barangmasuk.tglMasuk', $tahun);
            })
            ->when($jenis_barang, function ($q) use ($jenis_barang) {
                $q->where('databarang.IdJenisBarang', $jenis_barang);
            })
            ->select(
                'detail_barangmasuk.IdMasuk',
                'databarang.NamaBarang',
                'detail_barangmasuk.QtyMasuk',
                'barangmasuk.tglMasuk as tanggal_masuk'
            )
            ->orderBy('barangmasuk.tglMasuk', 'desc')
            ->orderBy('detail_barangmasuk.created_at', 'desc')
            ->get();

        // Get stock exit history with filters
        $riwayatKeluar = DB::table('detail_barangkeluar')
            ->join('databarang', 'detail_barangkeluar.IdBarang', '=', 'databarang.IdBarang')
            ->join('barangkeluar', 'detail_barangkeluar.IdKeluar', '=', 'barangkeluar.IdKeluar')
            ->when($bulan, function ($q) use ($bulan) {
                $q->whereMonth('barangkeluar.tglKeluar', $bulan);
            })
            ->when($tahun, function ($q) use ($tahun) {
                $q->whereYear('barangkeluar.tglKeluar', $tahun);
            })
            ->when($jenis_barang, function ($q) use ($jenis_barang) {
                $q->where('databarang.IdJenisBarang', $jenis_barang);
            })
            ->select(
                'detail_barangkeluar.IdKeluar',
                'databarang.NamaBarang',
                'detail_barangkeluar.QtyKeluar',
                'barangkeluar.tglKeluar as tanggal_keluar'
            )
            ->orderBy('barangkeluar.tglKeluar', 'desc')
            ->orderBy('detail_barangkeluar.created_at', 'desc')
            ->get();

        return view("admin.allitems", compact('items', 'riwayatStok', 'riwayatKeluar', 'jenisBarang'));
    }

    public function exportPdf(Request $request)
    {
        $bulan = $request->bulan;
        $tahun = $request->tahun;

        $items = Items::with(['jenisBarang', 'satuan'])->get();

        foreach ($items as $item) {
            $queryMasuk = DetailMasuk::where('IdBarang', $item->IdBarang)
                ->when($bulan, function ($q) use ($bulan) {
                    $q->whereMonth('created_at', $bulan);
                })
                ->when($tahun, function ($q) use ($tahun) {
                    $q->whereYear('created_at', $tahun);
                })
                ->orderBy('created_at', 'desc')
                ->first();

            $item->latestDetailMasuk = $queryMasuk;

            if ($item->latestDetailMasuk) {
                $item->latestDetailMasuk->load('supplier');
            }

            $item->latestDetailKeluar = DB::table('detail_barangkeluar')
                ->where('IdBarang', $item->IdBarang)
                ->orderBy('created_at', 'desc')
                ->first();
        }

        $items = $items->sortByDesc(function ($item) {
            return optional($item->latestDetailMasuk)->created_at;
        });

        // Buat PDF pakai view khusus
        $pdf = Pdf::loadView('admin.pdf_allitems', compact('items', 'bulan', 'tahun'));

        return $pdf->download('laporan_barang_' . now()->format('Ymd_His') . '.pdf');
    }

    public function SearchItem(Request $request)
    {
        $search = $request->search;

        $items = Items::where(function ($query) use ($search) {

            $query->where('id', 'like', "%$search%")
                ->orWhere('name', 'like', "%$search%");
        })->get();

        return view('admin.allitems', compact('items', 'search'));
    }

    public function AddItems()
    {
        $username = Auth::user()->username;
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
    }


    public function StoreItem(Request $request)
    {
        $request->validate([
            'IdBarang' => 'required|unique:databarang',
            'NamaBarang' => 'required|unique:databarang',
            'IdJenisBarang' => 'required',
            'IdSatuan' => 'required',
            'IdMasuk' => 'required',
            'username' => 'required',
            'IdSupplier' => 'required',
            'QtyMasuk' => 'required|numeric',
            'HargaSatuan' => 'required|numeric',
            'SubTotal' => 'required|numeric',
        ]);

        // Simpan ke tabel databarang (timestamps auto)
        Items::create([
            'IdBarang' => $request->IdBarang,
            'NamaBarang' => $request->NamaBarang,
            'IdJenisBarang' => $request->IdJenisBarang,
            'JumlahStok' => 0, // Tidak perlu diisi karena udah ada trigger sql
            'IdSatuan' => $request->IdSatuan,
        ]);

        // Simpan ke tabel barangmasuk (master transaksi, no timestamps)
        BarangMasuk::create([
            'IdMasuk' => $request->IdMasuk,
            'username' => $request->username,
            'tglMasuk' => Carbon::now(),
        ]);

        // Simpan ke tabel detail_barangmasuk (timestamps auto)
        DetailMasuk::create([
            'IdMasuk' => $request->IdMasuk,
            'IdSupplier' => $request->IdSupplier,
            'IdBarang' => $request->IdBarang,
            'QtyMasuk' => $request->QtyMasuk,
            'HargaSatuan' => $request->HargaSatuan,
            'SubTotal' => $request->SubTotal,
        ]);

        return redirect()->route('allitems')->with('message', 'Barang telah berhasil ditambah!');
    }

    public function detail($IdBarang)
    {
        // Ambil data barang dengan relasi yang dibutuhkan
        $item = Items::with(['jenisBarang', 'satuan'])
            ->where('IdBarang', $IdBarang)
            ->firstOrFail();

        // Ambil histori detail barang masuk (DetailMasuk) yang terkait barang ini, terbaru dulu
        $historiMasuk = DetailMasuk::with('supplier')
            ->where('IdBarang', $IdBarang)
            ->orderBy('created_at', 'desc')
            ->get();

        // Ambil histori detail barang keluar dari tabel detail_barangkeluar
        $historiKeluar = DB::table('detail_barangkeluar as dbk')
            ->join('barangkeluar as bk', 'dbk.IdKeluar', '=', 'bk.IdKeluar')
            ->select('dbk.*', 'bk.tglKeluar')
            ->where('dbk.IdBarang', $IdBarang)
            ->orderBy('bk.tglKeluar', 'desc')
            ->get();

        // Kirim ke view
        return view('admin.detail_allitems', compact('item', 'historiMasuk', 'historiKeluar'));
    }

    // Export PDF Detail Barang
    public function exportPdfDetail($id)
    {
        $item = Items::with(['jenisBarang', 'satuan'])->findOrFail($id);

        $historiMasuk = DetailMasuk::with('supplier')
                            ->where('IdBarang', $id)
                            ->orderBy('created_at', 'desc')
                            ->get();

        $historiKeluar = DB::table('detail_barangkeluar as dbk')
                            ->join('barangkeluar as bk', 'dbk.IdKeluar', '=', 'bk.IdKeluar')
                            ->select('dbk.*', 'bk.tglKeluar')
                            ->where('dbk.IdBarang', $id)
                            ->orderBy('bk.tglKeluar', 'desc')
                            ->get();

        $pdf = PDF::loadView('admin.pdf_detail', compact('item', 'historiMasuk', 'historiKeluar'))
                    ->setPaper('A4', 'portrait');

        return $pdf->stream('Detail_Barang_'.$item->NamaBarang.'.pdf');
    }



    public function EditItem($IdBarang)
    {

        $iteminfo = Items::findOrFail($IdBarang);
        $category_parent = $iteminfo->IdJenisBarang;
        // dd($category_parent);
        $parent_title = TypeItems::where('IdJenisBarang', $category_parent)->first();
        $category_parentS = $iteminfo->IdSatuan;
        $parent_titleS = Satuan::where('IdSatuan', $category_parentS)->first();
        // dd($iteminfo);
        $typeid = TypeItems::all();
        $typeS = Satuan::all();

        return view('admin.edititem', compact('iteminfo', 'typeid', 'typeS', 'parent_title', 'parent_titleS'));
    }

    public function UpdateItem(Request $request)
    {
        $itemid = $request->IdBarang;

        $request->validate([
            'NamaBarang' => [
                'required',
                Rule::unique('databarang', 'NamaBarang')->ignore($request->IdBarang, 'IdBarang'),
            ],

            'IdBarang' => 'required',
            'IdJenisBarang' => 'required',
            'IdSatuan' => 'required',
        ]);

        Items::where('IdBarang', $request->IdBarang)->update([
            'NamaBarang' => $request->NamaBarang,
            'IdJenisBarang' => $request->IdJenisBarang,
            'IdSatuan' => $request->IdSatuan,
        ]);

        return redirect()->route('allitems')->with('message', 'Update Informasi Barang Berhasil!');
    }

    public function DeleteItem($IdBarang)
    {
        // Ambil semua IdMasuk yang berkaitan dengan barang ini
        $idMasukList = DetailMasuk::where('IdBarang', $IdBarang)->pluck('IdMasuk');

        // Hapus semua entri detail masuk yang terkait dengan barang ini
        DetailMasuk::where('IdBarang', $IdBarang)->delete();

        // Hapus dari databarang
        Items::where('IdBarang', $IdBarang)->delete();

        // Cek apakah IdMasuk yang tadi sudah tidak digunakan lagi di detail_barangmasuk
        foreach ($idMasukList as $idMasuk) {
            $used = DetailMasuk::where('IdMasuk', $idMasuk)->exists();
            if (!$used) {
                BarangMasuk::where('IdMasuk', $idMasuk)->delete();
            }
        }

        return redirect()->route('allitems')->with('message', 'Penghapusan Barang ');
    }

    public function KeluarBarang()
    {
        $username = Auth::user()->username;
        $lastKeluar = DB::table('barangkeluar')->orderBy('IdKeluar', 'desc')->first();
        $newIdKeluar = $lastKeluar ? 'BK' . str_pad((int) substr($lastKeluar->IdKeluar, 2) + 1, 4, '0', STR_PAD_LEFT) : 'BK0001';

        $items = Items::with('jenisBarang', 'satuan')->get();

        return view('admin.keluaritems', compact('items', 'newIdKeluar', 'username'));
    }

    public function StoreKeluarBarang(Request $request)
    {
        $request->validate([
            'IdKeluar' => 'required',
            'username' => 'required',
            'IdBarang' => 'required',
            'QtyKeluar' => 'required|numeric|min:1',
        ]);

        // Check if stock is sufficient
        $item = Items::findOrFail($request->IdBarang);
        if ($item->JumlahStok < $request->QtyKeluar) {
            return redirect()->back()->with('error', 'Stok tidak mencukupi!');
        }

        // Begin transaction
        DB::beginTransaction();
        try {
            // Insert into barangkeluar (no timestamps)
            \App\Models\BarangKeluar::create([
                'IdKeluar' => $request->IdKeluar,
                'username' => $request->username,
                'tglKeluar' => Carbon::now(),
            ]);

            // Insert into detail_barangkeluar (timestamps auto)
            \App\Models\DetailKeluar::create([
                'IdKeluar' => $request->IdKeluar,
                'IdBarang' => $request->IdBarang,
                'QtyKeluar' => $request->QtyKeluar,
            ]);

            DB::commit();
            return redirect()->route('allitems')->with('message', 'Barang berhasil keluar!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function tambahQty(Request $request)
    {
        $request->validate([
            'IdBarang' => 'required',
            'QtyMasuk' => 'required|integer|min:1',
        ]);

        // Ambil IdMasuk terakhir dari tabel barangmasuk
        $lastMasuk = DB::table('barangmasuk')->orderByDesc('IdMasuk')->first();

        // Buat IdMasuk baru berdasarkan yang terakhir
        $newIdMasuk = $lastMasuk
            ? 'BM' . str_pad((int) substr($lastMasuk->IdMasuk, 2) + 1, 4, '0', STR_PAD_LEFT)
            : 'BM0001';

        // Ambil data detail masuk terakhir untuk IdBarang ini untuk mendapatkan HargaSatuan terbaru
        $latestDetailMasuk = DetailMasuk::where('IdBarang', $request->IdBarang)
            ->orderBy('created_at', 'desc')
            ->first();

        $hargaSatuan = $latestDetailMasuk ? $latestDetailMasuk->HargaSatuan : 0;
        $subTotal = $request->QtyMasuk * $hargaSatuan;

        // Ambil IdSupplier dari detail masuk terakhir, atau gunakan default jika tidak ada
        $idSupplier = $latestDetailMasuk ? $latestDetailMasuk->IdSupplier : 'SP0001';

        // Simpan data ke tabel barangmasuk
        DB::table('barangmasuk')->insert([
            'IdMasuk' => $newIdMasuk,
            'username' => auth()->user()->username ?? 'admin',
            'tglMasuk' => now(),
        ]);

        // Simpan data ke tabel detail_barangmasuk
        DB::table('detail_barangmasuk')->insert([
            'IdMasuk' => $newIdMasuk,
            'IdSupplier' => $idSupplier, // Gunakan IdSupplier yang diambil
            'IdBarang' => $request->IdBarang,
            'QtyMasuk' => $request->QtyMasuk,
            'HargaSatuan' => $hargaSatuan, // Gunakan HargaSatuan yang diambil
            'SubTotal' => $subTotal, // Gunakan SubTotal yang dihitung
        ]);

        return redirect()->back()->with('message', 'Qty berhasil ditambahkan!');
    }



    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Items';

    // /**
    //  * Make a grid builder.
    //  *
    //  * @return Grid
    //  */
    // protected function grid()
    // {
    //     $grid = new Grid(new Food());
    //     $grid->model()->latest();
    //     $grid->column('id', __('Id'));
    //     $grid->column('name', __('Name'));
    //      $grid->column('FoodType.title', __('Category'));
    //     $grid->column('price', __('Price'));
    //     //$grid->column('location', __('Location'));
    //     $grid->column('stars', __('Stars'));
    //     $grid->column('img', __('Thumbnail Photo'))->image('',60,60);
    //     $grid->column('description', __('Description'))->style('max-width:200px;word-break:break-all;')->display(function ($val){
    //         return substr($val,0,30);
    //     });
    //     //$grid->column('total_people', __('People'));
    //    // $grid->column('selected_people', __('Selected'));
    //     $grid->column('created_at', __('Created_at'));
    //     $grid->column('updated_at', __('Updated_at'));

    //     return $grid;
    // }

    // /**
    //  * Make a show builder.
    //  *
    //  * @param mixed $id
    //  * @return Show
    //  */
    // protected function detail($id)
    // {
    //     $show = new Show(Food::findOrFail($id));



    //     return $show;
    // }

    // /**
    //  * Make a form builder.
    //  *
    //  * @return Form
    //  */
    // protected function form()
    // {
    //     $form = new Form(new Food());
    //     $form->text('name', __('Name'));
    //       $form->select('type_id', __('Type_id'))->options((new FoodType())::selectOptions());
    //     $form->number('price', __('Price'));
    //     $form->text('location', __('Location'));
    //     $form->number('stars', __('Stars'));
    //     $form->number('people', __('People'));
    //     $form->number('selected_people', __('Selected'));
    //     $form->image('img', __('Thumbnail'))->uniqueName();
    //     $form->UEditor('description','Description');



    //     return $form;
    // }
}
