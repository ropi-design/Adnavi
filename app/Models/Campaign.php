<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    protected $fillable = [
        'ad_account_id',
        'campaign_id',
        'campaign_name',
        'campaign_type',
        'status',
        'budget_amount',
        'budget_type',
    ];

    protected function casts(): array
    {
        return [
            'budget_amount' => 'decimal:2',
        ];
    }

    /**
     * 広告アカウントとのリレーション
     */
    public function adAccount(): BelongsTo
    {
        return $this->belongsTo(AdAccount::class);
    }

    /**
     * 日次メトリクスとのリレーション
     */
    public function adMetricsDaily(): HasMany
    {
        return $this->hasMany(AdMetricsDaily::class);
    }

    /**
     * キャンペーンがアクティブかチェック
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * 指定期間の合計メトリクスを取得
     */
    public function getMetricsForPeriod(string $startDate, string $endDate): object
    {
        return $this->adMetricsDaily()
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw('
                SUM(impressions) as total_impressions,
                SUM(clicks) as total_clicks,
                SUM(cost) as total_cost,
                SUM(conversions) as total_conversions,
                AVG(ctr) as avg_ctr,
                AVG(cpc) as avg_cpc,
                AVG(cpa) as avg_cpa
            ')
            ->first();
    }
}
