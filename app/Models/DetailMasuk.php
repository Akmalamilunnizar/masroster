<?php
// app/Models/DetailMasuk.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailMasuk extends Model
{
    use HasFactory;
    protected $table = 'detail_barangmasuk';
    protected $fillable = [
        'IdRoster',
        'IdMasuk',
        'IdSupplier', // ini buat id
        'QtyMasuk',
        'HargaSatuan',
        'SubTotal'
    ];

    // relasi ke laporan
    // laporan() relation removed; Laporan model not present

    // If you really have no primary key, you can uncomment the next two lines:
    // public $incrementing = false;
    // protected $primaryKey = null;

    // Relationship to BarangMasuk
    public function barangMasuk()
    {
        return $this->belongsTo(BarangMasuk::class, 'IdMasuk', 'IdMasuk');
    }

    // Relationship to Supplier (now using users table)
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'IdSupplier', 'id');
    }

    // Relationship to Items (Barang)
    public function item()
    {
        return $this->belongsTo(Items::class, 'IdRoster', 'IdRoster');
    }
}