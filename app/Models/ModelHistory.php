<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_roster',
        'model_type',
        'version_id',
        'wmape_score',
        'mae_score',
        'rmse_score',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'wmape_score' => 'float',
        'mae_score' => 'float',
        'rmse_score' => 'float',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_roster', 'IdRoster');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForRoster($query, string $idRoster)
    {
        return $query->where('id_roster', $idRoster);
    }

    public function scopeForType($query, string $modelType)
    {
        return $query->where('model_type', strtolower($modelType));
    }
}
