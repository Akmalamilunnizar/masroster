<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiagnosaPenyakit extends Model
{
    use HasFactory;

    protected $table = 'diagnosa_penyakit';

    protected $fillable = [
        'id_penyakit',
        'id_koi',
        'name',
        'gambar_koi',
        'created_at',
        'updated_at'
    ];

    public function koiFish()
    {
        return $this->belongsTo(KoiFish::class, 'id_koi', 'id');
    }

    // Relationship with Penyakit (for id_penyakit)
    public function penyakit()
    {
        return $this->belongsTo(Penyakit::class, 'id_penyakit', 'id');
    }

    // Accessor to retrieve 'jenis_koi' from the related 'KoiFish'
    public function getJenisKoiAttribute()
    {
        return $this->koiFish->jenis_koi;
    }

    // Accessor to get 'koi_name' from related KoiFish
    public function getKoiNameAttribute()
    {
        return $this->koiFish->name;
    }

    // Accessor to get 'penyakit_name' from related Penyakit
    public function getPenyakitNameAttribute()
    {
        return $this->penyakit->name;
    }
}
