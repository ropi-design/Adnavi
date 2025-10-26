<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnalyticsProperty extends Model
{
    protected $fillable = [
        'user_id',
        'google_account_id',
        'property_id',
        'property_name',
        'timezone',
        'is_active',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_synced_at' => 'datetime',
        ];
    }

    /**
     * ユーザーとのリレーション
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Googleアカウントとのリレーション
     */
    public function googleAccount(): BelongsTo
    {
        return $this->belongsTo(GoogleAccount::class);
    }

    /**
     * 日次メトリクスとのリレーション
     */
    public function analyticsMetricsDaily(): HasMany
    {
        return $this->hasMany(AnalyticsMetricsDaily::class);
    }

    /**
     * 分析レポートとのリレーション
     */
    public function analysisReports(): HasMany
    {
        return $this->hasMany(AnalysisReport::class);
    }

    /**
     * プロパティがアクティブかチェック
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }
}
