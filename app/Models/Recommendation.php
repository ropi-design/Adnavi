<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recommendation extends Model
{
    protected $fillable = [
        'analysis_report_id',
        'insight_id',
        'title',
        'description',
        'action_type',
        'estimated_impact',
        'implementation_difficulty',
        'specific_actions',
        // Backward compatibility alias for views / code referencing implementation_steps
        'implementation_steps',
        'status',
        'implemented_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => \App\Enums\RecommendationStatus::class,
            'specific_actions' => 'array',
            'implemented_at' => 'datetime',
        ];
    }

    /**
     * インサイトとのリレーション
     */
    public function insight(): BelongsTo
    {
        return $this->belongsTo(Insight::class);
    }

    /**
     * 実施済みかチェック
     */
    public function isImplemented(): bool
    {
        return $this->status === 'implemented';
    }

    /**
     * 実施中かチェック
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * 却下されているかチェック
     */
    public function isDismissed(): bool
    {
        return $this->status === 'dismissed';
    }

    /**
     * 実施が簡単かチェック
     */
    public function isEasy(): bool
    {
        return $this->implementation_difficulty === 'easy';
    }

    /**
     * 実施を完了としてマーク
     */
    public function markAsImplemented(): void
    {
        $this->update([
            'status' => 'implemented',
            'implemented_at' => now(),
        ]);
    }

    /**
     * 互換アクセサ: implementation_steps を specific_actions にマッピング
     */
    public function getImplementationStepsAttribute(): array
    {
        return $this->specific_actions ?? [];
    }
}
