<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailTipe extends Model
{
    use HasFactory;

    protected $table = 'detail_tipe';
    public $timestamps = false;

    protected $fillable = [
        'id_jenis',
        'id_tipe',
    ];

    public function jenisRoster()
    {
        return $this->belongsTo(TypeItems::class, 'id_jenis', 'IdJenisBarang');
    }

    public function tipeRoster()
    {
        return $this->belongsTo(TipeRoster::class, 'id_tipe', 'IdTipe');
    }
}
