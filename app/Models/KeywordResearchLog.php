<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KeywordResearchLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'base_query',
        'status',
    ];

    /**
     * Get the metrics associated with the log.
     */
    public function metrics(): HasMany
    {
        return $this->hasMany(KeywordMetric::class, 'keyword_research_log_id');
    }
}
