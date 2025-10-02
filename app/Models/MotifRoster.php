<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MotifRoster extends Model
{
    use HasFactory;

    protected $table = 'motif_roster';
    protected $primaryKey = 'IdMotif';
    public $timestamps = false;

    protected $fillable = [
        'nama_motif',
    ];

    // Relationship with TipeRoster through detail_motif
    public function tipeRosters()
    {
        return $this->belongsToMany(TipeRoster::class, 'detail_motif', 'id_motif', 'id_tipe');
    }

    // Relationship with Produk
    public function produk()
    {
        return $this->hasMany(Produk::class, 'id_motif', 'IdMotif');
    }
}


