<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdMetricsDaily extends Model
{
    protected $table = 'ad_metrics_daily';

    protected $fillable = [
        'campaign_id',
        'date',
        'impressions',
        'clicks',
        'cost',
        'conversions',
        'conversion_value',
        'ctr',
        'cpc',
        'cpa',
        'roas',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'impressions' => 'integer',
            'clicks' => 'integer',
            'cost' => 'decimal:2',
            'conversions' => 'decimal:2',
            'conversion_value' => 'decimal:2',
            'ctr' => 'decimal:4',
            'cpc' => 'decimal:2',
            'cpa' => 'decimal:2',
            'roas' => 'decimal:4',
        ];
    }

    /**
     * キャンペーンとのリレーション
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}
