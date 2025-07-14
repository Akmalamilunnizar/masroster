<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Sensor extends Model
{
    protected $table = 'sensor'; // Nama tabel jika berbeda dengan nama model (opsional)

    // Menentukan primary key
    protected $primaryKey = 'id_sensor';  // Menetapkan kolom primary key sebagai 'id_sensor'

    // Jika id_sensor bukan integer, tentukan tipe datanya
    protected $keyType = 'int';  // Ubah sesuai tipe data primary key jika diperlukan (misalnya 'string')

    public $incrementing = false;  // Jika id_sensor bukan auto increment, set false

    protected $fillable = [
        'id_sensor',
        'temperature',
        'ph',
        'tds',
        'kekeruhan',
    ];

    protected $casts = [
        'id_sensor' => 'integer',
        'temperature' => 'integer',
        'ph' => 'integer',
        'tds' => 'integer',
        'kekeruhan' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function pond()
    {
        return $this->belongsTo(Pond::class);
    }

    // Menambahkan accessor atau mutator jika diperlukan

    public function getTemperatureAttribute($value)
    {
        return round($value, 2); // Membulatkan suhu hingga 2 angka desimal
    }






    // Jika Anda ingin menambahkan relasi dengan model lain, Anda dapat menambahkan metode seperti:
    // public function user()
    // {
    //     return $this->belongsTo(User::class, 'user_id');
    // }
}

