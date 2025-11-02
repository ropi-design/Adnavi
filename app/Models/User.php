<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'theme',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Get the user's avatar URL
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        return null;
    }

    /**
     * Check if user has an avatar
     */
    public function hasAvatar(): bool
    {
        return !empty($this->avatar);
    }

    /**
     * Googleアカウントとのリレーション
     */
    public function googleAccounts(): HasMany
    {
        return $this->hasMany(GoogleAccount::class);
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
     * 分析レポートとのリレーション
     */
    public function analysisReports(): HasMany
    {
        return $this->hasMany(AnalysisReport::class);
    }
}
