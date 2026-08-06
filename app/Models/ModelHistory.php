<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class ModelHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_roster',
        'produk_id',
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
        $foreignKey = Schema::hasColumn($this->getTable(), 'produk_id') ? 'produk_id' : 'id_roster';

        return $this->belongsTo(Produk::class, $foreignKey, (new Produk)->getKeyName());
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForRoster($query, string $idRoster)
    {
        $column = Schema::hasColumn($this->getTable(), 'produk_id') ? 'produk_id' : 'id_roster';

        return $query->where($column, $idRoster);
    }

    public function scopeForType($query, string $modelType)
    {
        return $query->where('model_type', strtolower($modelType));
    }
}
