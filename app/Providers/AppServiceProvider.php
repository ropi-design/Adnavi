<?php

namespace App\Providers;

use Carbon\Carbon;
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
    }
}
