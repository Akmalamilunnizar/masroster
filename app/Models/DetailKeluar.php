<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Laporan;

class DetailKeluar extends Model
{
    use HasFactory;
    protected $table = 'detail_barangkeluar';
    protected $fillable = [
        'IdBarang', // ini buat id
        'IdKeluar',
        'QtyKeluar'
    ];

    // relasi ke laporan
    public function laporan()
    {
        return $this->hasMany(Laporan::class, 'IdKeluar', 'IdKeluar');
    }
}
