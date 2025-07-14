<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'users';
    protected $primaryKey = 'id';  // <- PENTING: Ini harus sesuai nama kolom PK di DB
    // public $incrementing = false;         // Jika IdSatuan bukan auto increment
    // protected $keyType = 'string';        // Jika IdSatuan bertipe VARCHAR
    public $timestamps = false;

    protected $fillable = [
        'f_name',
        'email',
        'email_verified_at',
        'username',
        'user',
        'img',
        'nomor_telepon',
        'alamat',

    ];
}
