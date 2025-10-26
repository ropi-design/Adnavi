<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recommendation extends Model
{
    protected $fillable = [
        'insight_id',
        'title',
        'description',
        'action_type',
        'estimated_impact',
        'implementation_difficulty',
        'specific_actions',
        'status',
        'implemented_at',
    ];

    protected function casts(): array
    {
        return [
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
}
