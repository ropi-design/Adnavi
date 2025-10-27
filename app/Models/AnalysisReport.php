<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnalysisReport extends Model
{
    protected $fillable = [
        'user_id',
        'ad_account_id',
        'analytics_property_id',
        'report_type',
        'start_date',
        'end_date',
        'status',
        'overall_score',
        'summary',
        'raw_data',
        'analysis_result',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'raw_data' => 'array',
            'analysis_result' => 'array',
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
     * 広告アカウントとのリレーション
     */
    public function adAccount(): BelongsTo
    {
        return $this->belongsTo(AdAccount::class);
    }

    /**
     * Analyticsプロパティとのリレーション
     */
    public function analyticsProperty(): BelongsTo
    {
        return $this->belongsTo(AnalyticsProperty::class);
    }

    /**
     * インサイトとのリレーション
     */
    public function insights(): HasMany
    {
        return $this->hasMany(Insight::class);
    }

    /**
     * 改善施策とのリレーション
     */
    public function recommendations(): HasMany
    {
        return $this->hasMany(Recommendation::class);
    }

    /**
     * レポートが完了しているかチェック
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * レポートが失敗しているかチェック
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * レポートが処理中かチェック
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * 優先度の高いインサイトを取得
     */
    public function highPriorityInsights()
    {
        return $this->insights()->where('priority', 'high')->get();
    }
}
