<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail; // Anda bisa un-comment ini jika ingin menggunakan fitur verifikasi email Laravel

use Illuminate\Auth\Authenticatable as AuthenticableTrait;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

use Laravel\Passport\HasApiTokens;
use Laratrust\Traits\HasRolesAndPermissions;


class User extends Authenticatable implements CanResetPasswordContract
{
    use HasApiTokens, HasFactory, Notifiable, HasRolesAndPermissions, CanResetPassword, AuthenticableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'f_name',
        'email',
        'nomor_telepon', // Sudah ada, tidak perlu diduplikasi
        'alamat',        // Sudah ada, tidak perlu diduplikasi
        'password',
        'username',
        'user',          // Kolom 'user' untuk peran (role)
        'img'            // Kolom untuk gambar profil
    ];

    protected $attributes = [
        'img' => 'default-avatar.png'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false; // Set ke true jika Anda ingin menggunakan created_at dan updated_at

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'integer',
        // 'user_id' => 'integer', // Ini mungkin tidak diperlukan di sini kecuali ada kolom 'user_id' di tabel users
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relasi untuk Laratrust jika Anda menggunakan role_user_table
    public function roles()
    {
        return $this->morphToMany(
            config('laratrust.models.role'),
            'user', // Ini harus sesuai dengan nama morph 'user' di konfigurasi Laratrust Anda
            config('laratrust.tables.role_user'),
            'user_id',
            'role_id'
        );
    }

    // Jika Anda memiliki relasi satu-ke-banyak dengan Address, ini sudah benar
    public function addresses()
    {
        return $this->hasMany(Address::class, 'user_id', 'id');
    }

    public function defaultAddress()
    {
        return $this->hasOne(Address::class, 'user_id', 'id')->where('is_default', 1);
    }

    public function latestAddress()
    {
        return $this->hasOne(Address::class, 'user_id', 'id')->latestOfMany();
    }
}