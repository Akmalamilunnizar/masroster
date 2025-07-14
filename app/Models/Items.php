<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Laporan;

class Items extends Model
{
    use HasFactory;
    protected $table = 'databarang';
    protected $primaryKey = 'IdBarang';
    protected $fillable = [
        'IdBarang', // ini buat id
        'NamaBarang',
        'IdJenisBarang',
        'JumlahStok',
        'IdSatuan'
    ];

    public $timestamps = true;
    public function jenisBarang()
    {
        return $this->belongsTo(TypeItems::class, 'IdJenisBarang', 'IdJenisBarang');
    }

    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'IdSatuan', 'IdSatuan');
    }

    public function detailBarangMasuk()
    {
        return $this->hasOne(DetailMasuk::class, 'IdBarang', 'IdBarang');
    }

    public function detailBarangKeluar()
    {
        return $this->hasOne(DetailKeluar::class, 'IdBarang', 'IdBarang');
    }

    public function laporan()
    {
        return $this->hasMany(Laporan::class, 'IdBarang', 'IdBarang');
    }


}
