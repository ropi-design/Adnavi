<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsMetricsDaily extends Model
{
    protected $table = 'analytics_metrics_daily';

    protected $fillable = [
        'analytics_property_id',
        'date',
        'sessions',
        'users',
        'new_users',
        'pageviews',
        'bounce_rate',
        'avg_session_duration',
        'conversions',
        'conversion_rate',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'sessions' => 'integer',
            'users' => 'integer',
            'new_users' => 'integer',
            'pageviews' => 'integer',
            'bounce_rate' => 'decimal:2',
            'avg_session_duration' => 'decimal:2',
            'conversions' => 'decimal:2',
            'conversion_rate' => 'decimal:2',
        ];
    }

    /**
     * Analyticsプロパティとのリレーション
     */
    public function analyticsProperty(): BelongsTo
    {
        return $this->belongsTo(AnalyticsProperty::class);
    }
}
