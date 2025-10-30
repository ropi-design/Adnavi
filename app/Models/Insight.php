<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Insight extends Model
{
    protected $fillable = [
        'analysis_report_id',
        'category',
        'priority',
        'title',
        'description',
        'impact_score',
        'confidence_score',
        'data_points',
    ];

    protected function casts(): array
    {
        return [
            'category' => \App\Enums\InsightCategory::class,
            'priority' => \App\Enums\Priority::class,
            'impact_score' => 'integer',
            'confidence_score' => 'decimal:2',
            'data_points' => 'array',
        ];
    }

    /**
     * 分析レポートとのリレーション
     */
    public function analysisReport(): BelongsTo
    {
        return $this->belongsTo(AnalysisReport::class);
    }

    /**
     * 推奨事項とのリレーション
     */
    public function recommendations(): HasMany
    {
        return $this->hasMany(Recommendation::class);
    }

    /**
     * 優先度が高いかチェック
     */
    public function isHighPriority(): bool
    {
        return $this->priority === 'high';
    }

    /**
     * インパクトが大きいかチェック（7以上）
     */
    public function isHighImpact(): bool
    {
        return $this->impact_score >= 7;
    }

    /**
     * 信頼度が高いかチェック（0.7以上）
     */
    public function isHighConfidence(): bool
    {
        return $this->confidence_score >= 0.7;
    }
}
