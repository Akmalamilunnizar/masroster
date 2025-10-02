<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailHarga extends Model
{
    use HasFactory;

    protected $table = 'detail_harga';
    // Use id_roster as primary key for Laravel compatibility
    // The composite key (id_roster, id_user, id_ukuran) uniqueness is handled manually in the controller
    protected $primaryKey = 'id_roster';
    public $incrementing = false;

    protected $fillable = [
        'id_roster',
        'id_user',
        'id_ukuran',
        'harga'
    ];

    public $timestamps = false;

    // Relationships
    public function roster()
    {
        return $this->belongsTo(Produk::class, 'id_roster', 'IdRoster');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function size()
    {
        return $this->belongsTo(Size::class, 'id_ukuran', 'id_ukuran');
    }
}
