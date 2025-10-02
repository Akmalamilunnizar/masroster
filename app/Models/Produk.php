<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Items;
use App\Models\Size;
use App\Models\LaporanTransaksi;
use App\Models\TipeRoster;

class Produk extends Model
{
    // Nama tabel
    protected $table = 'produk';

    // Primary key bukan default 'id'
    protected $primaryKey = 'IdRoster';

    // Kalau primary key bukan auto-increment, disable incrementing
    public $incrementing = false;

    // Kalau primary key bukan integer
    protected $keyType = 'string';

    // Kolom yang bisa diisi
    protected $fillable = [
        'IdRoster',
        'id_jenis',
        'id_tipe',
        'id_motif',
        'JumlahStok',
        'Img',
        'deskripsi'
    ];

    // Kalau tidak pakai timestamps (created_at, updated_at)
    public $timestamps = true;

    // Relationships

    public function diskonRelasi()
    {
        return $this->belongsTo(Items::class, 'diskon', 'id');
    }

    public function jenisRoster()
    {
        return $this->belongsTo(TypeItems::class, 'id_jenis', 'IdJenisBarang');
    }

    public function tipeRoster()
    {
        return $this->belongsTo(TipeRoster::class, 'id_tipe', 'IdTipe');
    }

    public function motif()
    {
        return $this->belongsTo(MotifRoster::class, 'id_motif', 'IdMotif');
    }

    public function size()
    {
        return $this->belongsTo(Size::class, 'ukuran', 'id_ukuran');
    }


    public function sizes()
    {
        return $this->belongsToMany(\App\Models\Size::class, 'produk_size', 'IdRoster', 'id_ukuran')
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
