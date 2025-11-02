<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 日本語ロケール設定
        Carbon::setLocale(config('app.locale'));

        // 日付フォーマットのカスタマイズ
        if (config('app.locale') === 'ja') {
            setlocale(LC_TIME, 'ja_JP.UTF-8');
        }

        // Gemini APIレート制限の設定
        $this->configureGeminiRateLimiting();
    }

    /**
     * Gemini APIのレート制限を設定
     */
    protected function configureGeminiRateLimiting(): void
    {
        $rateLimit = config('gemini.rate_limit', [
            'requests_per_minute' => 60,
            'tokens_per_minute' => 32000,
        ]);

        RateLimiter::for('gemini', function (Request $request) use ($rateLimit) {
            return Limit::perMinute($rateLimit['requests_per_minute'])
                ->by($request->user()?->id ?: $request->ip());
        });
    }
}
