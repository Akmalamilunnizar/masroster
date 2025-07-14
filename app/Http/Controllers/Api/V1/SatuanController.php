<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\Satuan;
use Illuminate\Validation\Rule;
use Encore\Admin\Layout\Content;


class SatuanController extends Controller
{
    public function Index()
    {
        // Ambil jumlah ikan per kolam berdasarkan pond_id
        // $jml_ikan = DB::table('detail_koi')
        //     ->selectRaw('count(*) as jml_ikan, pond_id')
        //     ->groupBy('pond_id')
        //     ->get();

        // Ambil data kolam
        $satuan = Satuan::all();

        // Kirim data ke view
        return view("admin.allsatuan", compact('satuan'));
    }


    public function SearchSatuan(Request $request)
    {
        $search = $request->search;

        $satuan = Satuan::where(function ($query) use ($search) {

            $query->where('id', 'like', "%$search%")
                ->orWhere('name', 'like', "%$search%");
        })->get();

        return view('admin.allsatuan', compact('satuan', 'search'));
    }

    public function AddSatuan()
    {
        $typeS = Satuan::all();

        return view("admin.addsatuan", compact('typeS'));
    }

    public function StoreSatuan(Request $request)
    {
        // Ambil ID terakhir
        $latestSatuan = Satuan::orderBy('IdSatuan', 'desc')->first();

        if (!$latestSatuan) {
            $newId = 'S0001';
        } else {
            // Ambil angka dari ID terakhir
            $number = intval(substr($latestSatuan->IdSatuan, 3)) + 1;
            $newId = 'S' . str_pad($number, 4, '0', STR_PAD_LEFT);
        }

        $request->validate([
            'Satuan' => 'required|unique:satuan',
            // 'IdSatuan' => 'required',
        ]);

        Satuan::create([
            'IdSatuan' => $newId,
            'Satuan' => $request->Satuan,
        ]);

        return redirect()->route('allsatuan')->with('message', 'Satuan berhasil ditambahkan!');
    }


    public function EditSatuan($IdSatuan)
    {

        $satuaninfo = Satuan::findOrFail($IdSatuan);

        $category_parentS = $satuaninfo->IdSatuan;
        $parent_titleS = Satuan::where('IdSatuan', $category_parentS)->first();
        // dd($satuaninfo);

        $typeS = Satuan::all();

        return view('admin.editsatuan', compact('satuaninfo', 'typeS', 'parent_titleS'));
    }

    public function UpdateSatuan(Request $request)
    {
        $request->validate([
            'IdSatuan' => ['required', Rule::unique('satuan')->ignore($request->original_id, 'IdSatuan')],
            'Satuan' => ['required'],
        ]);

        Satuan::where('IdSatuan', $request->original_id)->update([
            'IdSatuan' => $request->IdSatuan,
            'Satuan' => $request->Satuan,
        ]);

        return redirect()->route('allsatuan')->with('message', 'Update Informasi Satuan Berhasil!');
    }


    public function DeleteSatuan($IdSatuan)
    {
        Satuan::findOrFail($IdSatuan)->delete();

        return redirect()->route('allsatuan')->with('message', 'Penghapusan Satuan Berhasil!');
    }



    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Satuan';

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
