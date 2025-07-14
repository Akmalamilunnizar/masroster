<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Laporan;

class Supplier extends Model
{
    use HasFactory;

    // Nama tabel
    protected $table = 'supplier';

    // Primary key kustom (bukan 'id')
    protected $primaryKey = 'IdSupplier';

    // Primary key bukan auto increment (kalau string seperti kode)
    public $incrementing = false;

    // Tipe data primary key (defaultnya integer)
    protected $keyType = 'string';

    public $timestamps = false;

    // Field yang bisa diisi mass-assignment
    protected $fillable = [
        'IdSupplier',
        'NamaSupplier',
        'NoTelp',
        'Alamat',
    ];

    public function laporan()
    {
        return $this->hasMany(Laporan::class, 'IdSupplier', 'IdSupplier');
    }

}
