<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailTransaksi extends Model
{
    protected $table = 'detail_transaksi';
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'IdTransaksi',
        'IdProduk',
        'id_ukuran',
        'CustomUkuran',
        'QtyProduk',
        'SubTotal',
        'design_file'
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'IdTransaksi', 'IdTransaksi');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'IdProduk', 'IdProduk');
    }

    public function size()
    {
        return $this->belongsTo(Size::class, 'id_ukuran', 'id_ukuran');
    }
} 