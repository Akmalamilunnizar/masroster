<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Laporan;

class Supplier extends Model
{
    use HasFactory;

    // Nama tabel
    protected $table = 'users';

    // Primary key kustom (bukan 'id')
    protected $primaryKey = 'id';

    // Primary key auto increment
    public $incrementing = true;

    // Tipe data primary key
    protected $keyType = 'int';

    public $timestamps = false;

    // Field yang bisa diisi mass-assignment
    protected $fillable = [
        'id',
        'f_name',
        'email',
        'nomor_telepon',
        'username',
        'password',
        'user',
        'img'
    ];

    // Scope untuk hanya mengambil user dengan role "User" (suppliers)
    public function scopeSuppliers($query)
    {
        return $query->where('user', 'User');
    }

    // Accessor untuk IdSupplier (menggunakan id)
    public function getIdSupplierAttribute()
    {
        return 'SP' . str_pad($this->id, 4, '0', STR_PAD_LEFT);
    }

    // Accessor untuk NamaSupplier (menggunakan f_name)
    public function getNamaSupplierAttribute()
    {
        return $this->f_name;
    }

    // Accessor untuk NoTelp (menggunakan nomor_telepon)
    public function getNoTelpAttribute()
    {
        return $this->nomor_telepon;
    }

    // Accessor untuk Alamat (menggunakan default value karena alamat tidak ada di tabel)
    public function getAlamatAttribute()
    {
        return 'Alamat tidak tersedia';
    }

    // Mutator untuk NamaSupplier (menggunakan f_name)
    public function setNamaSupplierAttribute($value)
    {
        $this->attributes['f_name'] = $value;
    }

    // Mutator untuk NoTelp (menggunakan nomor_telepon)
    public function setNoTelpAttribute($value)
    {
        $this->attributes['nomor_telepon'] = $value;
    }

    // Mutator untuk Alamat (tidak melakukan apa-apa karena alamat tidak ada di tabel)
    public function setAlamatAttribute($value)
    {
        // Do nothing since alamat field doesn't exist in users table
    }

    public function laporan()
    {
        return $this->hasMany(Laporan::class, 'IdSupplier', 'id');
    }
}
