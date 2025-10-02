<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailMotif extends Model
{
    use HasFactory;

    protected $table = 'detail_motif';
    public $timestamps = false;

    protected $fillable = [
        'id_tipe',
        'id_motif',
    ];

    public function tipeRoster()
    {
        return $this->belongsTo(TipeRoster::class, 'id_tipe', 'IdTipe');
    }

    public function motifRoster()
    {
        return $this->belongsTo(MotifRoster::class, 'id_motif', 'IdMotif');
    }
}
