<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Items;
use App\Models\Size;
use App\Models\LaporanTransaksi;

class Produk extends Model
{
    // Nama tabel
    protected $table = 'produk';

    // Primary key bukan default 'id'
    protected $primaryKey = 'IdProduk';

    // Kalau primary key bukan auto-increment, disable incrementing
    public $incrementing = false;

    // Kalau primary key bukan integer
    protected $keyType = 'string';

    // Kolom yang bisa diisi
    protected $fillable = [
        'IdProduk',
        'NamaProduk',
        'custom_harga',
        'id_bahan',
        'Img',
        'deskripsi'
    ];

    // Kalau tidak pakai timestamps (created_at, updated_at)
    public $timestamps = false;

    // Relationships

    public function diskonRelasi()
    {
        return $this->belongsTo(Items::class, 'diskon', 'id');
    }
    public function bahan()
    {
        return $this->belongsTo(Items::class, 'id_bahan', 'IdBarang');
    }

    public function size()
    {
        return $this->belongsTo(Size::class, 'ukuran', 'id_ukuran');
    }

    public function laporantransaksi()
    {
        return $this->hasMany(LaporanTransaksi::class, 'IdProduk', 'IdProduk');
    }

    public function sizes()
    {
        return $this->belongsToMany(\App\Models\Size::class, 'produk_size', 'IdProduk', 'id_ukuran')
                    ->withPivot('harga')
                    ->withTimestamps();
    }
    public function transaksi()
    {
        return $this->belongsToMany(Transaksi::class, 'detail_transaksi', 'IdProduk', 'IdTransaksi')
            ->withPivot(['QtyProduk', 'SubTotal'])
            ->withTimestamps()
        ;
    }

}
