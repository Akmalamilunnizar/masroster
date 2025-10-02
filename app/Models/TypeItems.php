<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeItems extends Model
{
    use HasFactory;

    protected $table = 'jenisbarang';
    protected $primaryKey = 'IdJenisBarang';
    public $incrementing = true;         // Now auto increment
    protected $keyType = 'int';          // Now integer type
    public $timestamps = false;

    protected $fillable = [
        'JenisBarang',
    ];
}
