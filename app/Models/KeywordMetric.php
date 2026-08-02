<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KeywordMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'keyword_research_log_id',
        'keyword',
        'search_volume',
        'cpc',
        'competition',
        'monthly_trend',
    ];

    protected $casts = [
        'monthly_trend' => 'array',
        'search_volume' => 'integer',
        'cpc' => 'decimal:2',
    ];

    /**
     * Get the research log that owns the metric.
     */
    public function researchLog(): BelongsTo
    {
        return $this->belongsTo(KeywordResearchLog::class, 'keyword_research_log_id');
    }
}
