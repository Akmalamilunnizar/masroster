<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\LaporanTransaksi;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksi';
    protected $primaryKey = 'IdTransaksi';  // <- PENTING: Ini harus sesuai nama kolom PK di DB
    public $incrementing = false;
    // protected $keyType = 'string';        // Jika IdSatuan bertipe VARCHAR
    public $timestamps = false;

    protected $fillable = [
        'IdTransaksi',
        'username',
        'id',
        'address_id',
        'alamat_pengiriman',
        'Bayar',
        'GrandTotal',
        'tglTransaksi',
        'StatusPembayaran',
        'StatusPesanan',
        'tglUpdate',
        'notes',
        'shipping_method',
        'shipping_type'
    ];

    public function detailTransaksi()
    {
        return $this->hasMany(DetailTransaksi::class, 'IdTransaksi', 'IdTransaksi');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'IdCust', 'IdCust');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'username', 'username');
    }
    public function detail()
    {
        return $this->belongsTo(User::class, 'username', 'username');
    }


    public function laporantransaksi()
    {
        return $this->hasMany(LaporanTransaksi::class, 'IdTransaksi', 'IdTransaksi');
    }

    public function produk()
    {
        return $this->belongsToMany(Produk::class, 'detail_transaksi', 'IdTransaksi', 'IdProduk')
            ->withPivot(['QtyProduk', 'SubTotal']) // alias pivot
            // ->withTimestamps()
        ;
    }

    public function address()
    {
        return $this->belongsTo(\App\Models\Address::class, 'address_id', 'id');
    }

}
