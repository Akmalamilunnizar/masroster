<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TypeItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TypeItemsController extends Controller
{
    public function Index()
    {
        $type = TypeItems::all();

        return view('admin.alltype', compact('type'));
    }

    public function SearchType(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));

        if ($search === '') {
            $type = TypeItems::all();

            return view('admin.alltype', compact('type', 'search'));
        }

        $type = TypeItems::where(function ($query) use ($search) {
            $query->where('IdJenisBarang', 'like', "%{$search}%")
                ->orWhere('JenisBarang', 'like', "%{$search}%");
        })->get();

        return view('admin.alltype', compact('type', 'search'));
    }

    public function SearchItem(Request $request)
    {
        return $this->SearchType($request);
    }

    public function AddType()
    {
        $typeid = TypeItems::all();

        return view('admin.addtype', compact('typeid'));
    }

    public function StoreType(Request $request)
    {
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
        $parent_title = TypeItems::where('IdJenisBarang', $category_parent)->first();
        $typeid = TypeItems::all();

        return view('admin.edittype', compact('typeinfo', 'typeid', 'parent_title'));
    }

    public function UpdateType(Request $request)
    {
        $validated = $request->validate([
            'original_id' => 'required|integer|exists:jenisbarang,IdJenisBarang',
            'JenisBarang' => [
                'required',
                'string',
                'max:50',
                Rule::unique('jenisbarang', 'JenisBarang')->ignore($request->original_id, 'IdJenisBarang'),
            ],
        ]);

        $oldData = TypeItems::where('IdJenisBarang', $validated['original_id'])->first();

        if (! $oldData) {
            return redirect()->route('alltype')->with('error', 'Data tidak ditemukan.');
        }

        // Cek apakah ada perubahan
        if ($oldData->JenisBarang === $validated['JenisBarang']) {
            return redirect()->route('alltype')->with('message', 'Tidak ada perubahan yang dilakukan.');
        }

        // Update data jika ada perubahan (hanya JenisBarang)
        TypeItems::where('IdJenisBarang', $validated['original_id'])->update([
            'JenisBarang' => $validated['JenisBarang'],
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
            'type_ids.*' => 'required|integer|exists:jenisbarang,IdJenisBarang',
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
                $errors[] = "Gagal menghapus jenis barang dengan ID: $typeId - ".$e->getMessage();
            }
        }

        if (count($errors) > 0) {
            return redirect()->route('alltype')->with('message', 'Beberapa jenis barang gagal dihapus: '.implode(', ', $errors))->with('alert', 'warning');
        }

        return redirect()->route('alltype')->with('message', "Berhasil menghapus $deletedCount jenis barang!")->with('alert', 'success');
    }

    public function quickAddJenis(Request $request)
    {
        try {
            $validated = $request->validate([
                'JenisBarang' => 'required|unique:jenisbarang,JenisBarang',
            ]);

            $jenis = TypeItems::create([
                'JenisBarang' => $validated['JenisBarang'],
            ]);

            return response()->json([
                'success' => true,
                'id' => $jenis->IdJenisBarang,
                'name' => $jenis->JenisBarang,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
