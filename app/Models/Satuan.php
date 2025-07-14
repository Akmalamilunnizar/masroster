<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Satuan extends Model
{
    use HasFactory;

    protected $table = 'satuan';
    protected $primaryKey = 'IdSatuan';  // <- PENTING: Ini harus sesuai nama kolom PK di DB
    public $incrementing = false;         // Jika IdSatuan bukan auto increment
    protected $keyType = 'string';        // Jika IdSatuan bertipe VARCHAR
    public $timestamps = false;

    protected $fillable = [
        'IdSatuan',
        'Satuan',
    ];
}
