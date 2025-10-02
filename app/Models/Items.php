<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Items extends Model
{
    use HasFactory;
    protected $table = 'produk';
    protected $primaryKey = 'IdRoster';
    protected $fillable = [
        'IdRoster',
        'id_jenis',
        'id_tipe',
        'id_motif',
        'JumlahStok',
        'Img',
        'deskripsi',
    ];

    public $timestamps = true;
    public function jenisRoster()
    {
        return $this->belongsTo(TypeItems::class, 'id_jenis', 'IdJenisBarang');
    }



    public function detailBarangMasuk()
    {
        return $this->hasOne(DetailMasuk::class, 'IdRoster', 'IdRoster');
    }

    public function detailBarangKeluar()
    {
        return $this->hasOne(DetailKeluar::class, 'IdRoster', 'IdRoster');
    }

    // laporan() relation removed; Laporan model not present

    public function bahan()
    {
        return $this->belongsTo(Items::class, 'id_bahan', 'IdBarang');
    }
    public function size()
    {
        return $this->belongsTo(Size::class, 'ukuran', 'id_ukuran');
    }
}
