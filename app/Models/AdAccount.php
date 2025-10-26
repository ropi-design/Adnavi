<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdAccount extends Model
{
    protected $fillable = [
        'user_id',
        'google_account_id',
        'customer_id',
        'account_name',
        'currency',
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
     * キャンペーンとのリレーション
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    /**
     * 分析レポートとのリレーション
     */
    public function analysisReports(): HasMany
    {
        return $this->hasMany(AnalysisReport::class);
    }

    /**
     * アカウントがアクティブかチェック
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * 最後の同期から指定時間以上経過しているかチェック
     */
    public function needsSync(int $hours = 24): bool
    {
        if (!$this->last_synced_at) {
            return true;
        }

        return $this->last_synced_at->addHours($hours)->isPast();
    }
}
