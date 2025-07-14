<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Laporan;

class BarangKeluar extends Model
{
    use HasFactory;
    protected $table = 'barangkeluar';
    protected $primaryKey = 'IdKeluar';
    protected $fillable = [
        'IdKeluar',
        'username', // ini buat id
        'tglKeluar',
    ];


    public function laporan()
    {
        return $this->hasMany(Laporan::class, 'IdOut', 'idkeluar');
    }

}
