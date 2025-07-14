<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penyakit extends Model
{
    use HasFactory;

    protected $table = 'penyakit';
    protected $fillable = [
        // 'id',
        'nama_penyakit',
        // 'description',
    ];
    // Relasi dengan KoiFish
    public function koiFish()
    {
        return $this->hasMany(KoiFish::class, 'id_penyakit');  // Relasi one-to-many dengan tabel koi_fish
    }

    public function jenisPenyakit()
    {
        return $this->belongsTo(Penyakit::class, 'penyakit');
    }
    public $timestamps = false; // Nonaktifkan penggunaan timestamps
}
