<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'id_admin',
        'id_customer',
        'address_id',
        'Bayar',
        'GrandTotal',
        'tglTransaksi',
        'StatusPembayaran',
        'StatusPesanan',
        'tglUpdate',
        'notes',
        'shipping_method',
        'delivery_method',
        'shipping_type',
        'ongkir',
    ];

    protected $casts = [
        'tglTransaksi' => 'datetime',
        'tglUpdate' => 'datetime',
    ];

    public function detailTransaksi()
    {
        return $this->hasMany(DetailTransaksi::class, 'IdTransaksi', 'IdTransaksi');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'id_customer', 'id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'id_admin', 'id');
    }
    
    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id', 'id');
    }
    
    public function detail()
    {
        return $this->hasMany(DetailTransaksi::class, 'IdTransaksi', 'IdTransaksi');
    }

    // laporantransaksi() relation removed; LaporanTransaksi model not present

    public function produk()
    {
        return $this->belongsToMany(Produk::class, 'detail_transaksi', 'IdTransaksi', 'IdProduk')
            ->withPivot(['QtyProduk', 'SubTotal']) // alias pivot
            // ->withTimestamps()
        ;
    }



}
