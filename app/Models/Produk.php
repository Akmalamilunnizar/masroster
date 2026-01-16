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
        'stock',
        'Img',
        'deskripsi',
        'NamaProduk'
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

    /**
     * Generate combined product name from jenis, tipe, and motif
     */
    public function generateNamaProduk()
    {
        $parts = [];

        // Add jenis (jenisbarang)
        if ($this->jenisRoster) {
            $parts[] = $this->jenisRoster->JenisBarang;
        }

        // Add tipe (tipe_roster)
        if ($this->tipeRoster) {
            $parts[] = $this->tipeRoster->namaTipe;
        }

        // Add motif (motif_roster)
        if ($this->motif) {
            $parts[] = $this->motif->nama_motif;
        }

        return implode(' ', $parts);
    }

    /**
     * Boot method to auto-generate NamaProduk when creating/updating
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($produk) {
            if (empty($produk->NamaProduk)) {
                $produk->NamaProduk = $produk->generateNamaProduk();
            }
        });

        static::updating(function ($produk) {
            // Only auto-generate if NamaProduk is empty or if related fields changed
            if (empty($produk->NamaProduk) ||
                $produk->isDirty(['id_jenis', 'id_tipe', 'id_motif'])) {
                $produk->NamaProduk = $produk->generateNamaProduk();
            }
        });
    }

}
