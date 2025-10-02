<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailKeluar extends Model
{
    use HasFactory;
    protected $table = 'detail_barangkeluar';
    protected $fillable = [
        'IdRoster', // ini buat id
        'IdKeluar',
        'QtyKeluar'
    ];

    // relasi ke laporan
    // laporan() relation removed; Laporan model not present
}
