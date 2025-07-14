<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Diskon extends Model
{
    use HasFactory;

    protected $table = 'diskon';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'nama',
        'description',
        'persentase'
    ];

    // Relationship with Produk
    public function produk()
    {
        return $this->hasMany(Produk::class, 'diskon', 'id');
    }
} 