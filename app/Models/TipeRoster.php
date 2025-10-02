<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipeRoster extends Model
{
    use HasFactory;

    protected $table = 'tipe_roster';
    protected $primaryKey = 'IdTipe';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'IdTipe',
        'namaTipe',
    ];

    // Relationship with Produk
    public function produk()
    {
        return $this->hasMany(Produk::class, 'id_tipe', 'IdTipe');
    }

    // Relationship with JenisRoster through detail_tipe
    public function jenisRosters()
    {
        return $this->belongsToMany(TypeItems::class, 'detail_tipe', 'id_tipe', 'id_jenis');
    }

    // Relationship with MotifRoster through detail_motif
    public function motifRosters()
    {
        return $this->belongsToMany(MotifRoster::class, 'detail_motif', 'id_tipe', 'id_motif');
    }
}
