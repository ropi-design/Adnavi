<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoogleAccount extends Model
{
    protected $fillable = [
        'user_id',
        'google_id',
        'email',
        'access_token',
        'refresh_token',
        'token_expires_at',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    protected function casts(): array
    {
        return [
            'token_expires_at' => 'datetime',
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
    public function adAccounts(): HasMany
    {
        return $this->hasMany(AdAccount::class);
    }

    /**
     * Analyticsプロパティとのリレーション
     */
    public function analyticsProperties(): HasMany
    {
        return $this->hasMany(AnalyticsProperty::class);
    }

    /**
     * トークンが有効かチェック
     */
    public function isTokenValid(): bool
    {
        return $this->token_expires_at && $this->token_expires_at->isFuture();
    }

    /**
     * トークンの有効期限が切れているかチェック
     */
    public function isTokenExpired(): bool
    {
        return !$this->isTokenValid();
    }
}
