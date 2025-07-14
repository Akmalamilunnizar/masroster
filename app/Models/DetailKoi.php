<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailKoi extends Model
{
    use HasFactory;
    protected $table = 'detail_koi'; // Nama tabel, jika berbeda

    // Relasi ke KoiFish
    public function koiFish()
    {
        return $this->belongsTo(KoiFish::class, 'fish_id');
    }

      // Relasi ke Pond
      public function pond()
      {
          return $this->belongsTo(Pond::class, 'pond_id');
      }
}
