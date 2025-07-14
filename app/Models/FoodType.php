<?php
namespace App\Models;
// use Encore\Admin\Traits\DefaultDatetimeFormat;
// use Encore\Admin\Traits\ModelTree;
use Illuminate\Database\Eloquent\Model;

class FoodType extends Model
{
    //
    // use DefaultDatetimeFormat;
    // use ModelTree;
    //table name
    protected $fillable = [
        'title',
        'parent_id',
        'order',
        'description',
    ];
    protected $table = 'food_types';

    public function getList(){
        return $this->get();
    }

    // public function children()
    // {
    //     return $this->hasMany(FoodType::class, 'parent_id');
    // }

    //     public function parent()
    // {
    //     return $this->belongsTo(FoodType::class, 'parent_id');
    // }

}
