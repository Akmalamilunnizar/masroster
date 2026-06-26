<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class DetailTransaksi extends Model
{
    protected $table = 'detail_transaksi';
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'IdTransaksi',
        'IdRoster',
        'produk_id',
        'id_ukuran',
        'QtyProduk',
        'data_type',
        'SubTotal',
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'IdTransaksi', 'IdTransaksi');
    }

    public function produk()
    {
        $foreignKey = Schema::hasColumn($this->getTable(), 'produk_id') ? 'produk_id' : 'IdRoster';

        return $this->belongsTo(Produk::class, $foreignKey, (new Produk())->getKeyName());
    }

    public function size()
    {
        return $this->belongsTo(Size::class, 'id_ukuran', 'id_ukuran');
    }
}
