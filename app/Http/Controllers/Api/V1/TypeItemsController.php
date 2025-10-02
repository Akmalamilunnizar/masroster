<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\TypeItems;
use Illuminate\Validation\Rule;
use Encore\Admin\Layout\Content;


class TypeItemsController extends Controller
{
    public function Index()
    {
        // Ambil jumlah ikan per kolam berdasarkan pond_id
        // $jml_ikan = DB::table('detail_koi')
        //     ->selectRaw('count(*) as jml_ikan, pond_id')
        //     ->groupBy('pond_id')
        //     ->get();

        // Ambil data kolam
        $type = TypeItems::all();

        // Kirim data ke view
        return view("admin.alltype", compact('type'));
    }


    public function SearchItem(Request $request)
    {
        $search = $request->search;

        $type = TypeItems::where(function ($query) use ($search) {

            $query->where('id', 'like', "%$search%")
                ->orWhere('name', 'like', "%$search%");
        })->get();

        return view('admin.alltype', compact('type', 'search'));
    }

    public function AddType()
    {
        $typeid = TypeItems::all();

        return view("admin.addtype", compact('typeid'));
    }

    public function StoreType(Request $request)
    {
        // dd($request->all());

        $request->validate([
            'JenisBarang' => 'required|unique:jenisbarang,JenisBarang',
        ]);

        TypeItems::create([
            'JenisBarang' => $request->JenisBarang,
        ]);

        return redirect()->route('alltype')->with('message', 'Barang telah berhasil ditambah!');
    }

    public function EditType($IdJenisBarang)
    {

        $typeinfo = TypeItems::findOrFail($IdJenisBarang);
        $category_parent = $typeinfo->IdJenisBarang;
        // dd($category_parent);
        $parent_title = TypeItems::where('IdJenisBarang', $category_parent)->first();
        // dd($iteminfo);
        $typeid = TypeItems::all();

        return view('admin.edittype', compact('typeinfo', 'typeid', 'parent_title'));
    }



public function UpdateType(Request $request)
{
    // Ambil data lama dari database
    $oldData = TypeItems::where('IdJenisBarang', $request->original_id)->first();

    if (!$oldData) {
        return redirect()->route('alltype')->with('error', 'Data tidak ditemukan.');
    }

    // Validasi
    $request->validate([
        'JenisBarang' => [
            'required',
            Rule::unique('jenisbarang', 'JenisBarang')->ignore($request->original_id, 'IdJenisBarang'),
        ],
    ]);

    // Cek apakah ada perubahan
    if ($oldData->JenisBarang === $request->JenisBarang) {
        return redirect()->route('alltype')->with('message', 'Tidak ada perubahan yang dilakukan.');
    }

    // Update data jika ada perubahan (hanya JenisBarang)
    TypeItems::where('IdJenisBarang', $request->original_id)->update([
        'JenisBarang' => $request->JenisBarang,
    ]);

    return redirect()->route('alltype')->with('message', 'Update Informasi Jenis Barang Berhasil!');
}


    public function DeleteType($IdJenisBarang)
    {
        TypeItems::findOrFail($IdJenisBarang)->delete();

        return redirect()->route('alltype')->with('message', 'Penghapusan Barang Berhasil!');
    }

    public function get_item_list()
    {
        $item = TypeItems::get(); // Retrieve all records from the 'item' table

        return response()->json($item, 200);
    }

    public function updateRelayCondition(Request $request)
    {
        $request->validate([
            'item_id' => 'required|integer',
            'relay_condition' => 'required|boolean',
        ]);

        $item = TypeItems::find($request->item_id);

        if ($item) {
            $item->relay_condition = $request->relay_condition;
            $item->save();

            return response()->json([
                'message' => 'Relay condition updated successfully.',
                'item' => $item,
            ], 200);
        }

        return response()->json([
            'message' => 'TypeItems not found.',
        ], 404);
    }



    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'TypeItems';

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
    //     }

    public function batchDelete(Request $request)
    {
        $request->validate([
            'type_ids' => 'required|array',
            'type_ids.*' => 'required|string'
        ]);

        $deletedCount = 0;
        $errors = [];

        foreach ($request->type_ids as $typeId) {
            try {
                // Check if this type is being used by any items
                $itemsUsingType = \App\Models\Produk::where('id_jenis', $typeId)->count();
                
                if ($itemsUsingType > 0) {
                    $errors[] = "Jenis barang dengan ID: $typeId tidak dapat dihapus karena masih digunakan oleh $itemsUsingType item";
                    continue;
                }

                // Delete the type
                \App\Models\TypeItems::where('IdJenisBarang', $typeId)->delete();
                $deletedCount++;
            } catch (\Exception $e) {
                $errors[] = "Gagal menghapus jenis barang dengan ID: $typeId - " . $e->getMessage();
            }
        }

        if (count($errors) > 0) {
            return redirect()->route('alltype')->with('message', 'Beberapa jenis barang gagal dihapus: ' . implode(', ', $errors))->with('alert', 'warning');
        }

        return redirect()->route('alltype')->with('message', "Berhasil menghapus $deletedCount jenis barang!")->with('alert', 'success');
    }

    public function quickAddJenis(Request $request)
    {
        try {
            $request->validate([
                'JenisBarang' => 'required|unique:jenisbarang,JenisBarang'
            ]);

            $jenis = TypeItems::create([
                'JenisBarang' => $request->JenisBarang
            ]);

            return response()->json([
                'success' => true,
                'id' => $jenis->IdJenisBarang,
                'name' => $jenis->JenisBarang
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
