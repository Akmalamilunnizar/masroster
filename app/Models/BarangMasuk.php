<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Laporan;

class BarangMasuk extends Model
{
    use HasFactory;
    protected $table = 'barangmasuk';
    protected $primaryKey = 'IdMasuk';
    public $timestamps = false;
    protected $fillable = [
        'IdMasuk',
        'username', // ini buat id
        'tglMasuk',
    ];


    public function laporan()
    {
        return $this->hasMany(Laporan::class, 'IdIn', 'idmasuk');
    }

}
