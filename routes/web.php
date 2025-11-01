<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return redirect('/dashboard');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // ダッシュボード
    Volt::route('dashboard', 'dashboard.overview')->name('dashboard');
});

Route::middleware(['auth'])->group(function () {
    // アカウント管理
    Route::prefix('accounts')->name('accounts.')->group(function () {
        Volt::route('google', 'accounts.connect-google')->name('google');
        Volt::route('ads', 'accounts.ad-account-list')->name('ads');
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Volt::route('{id}', 'accounts.analytics-property-detail')->name('show');
        });
        Volt::route('analytics', 'accounts.analytics-property-list')->name('analytics');
    });

    // Google OAuth
    Route::get('auth/google', [App\Http\Controllers\GoogleAuthController::class, 'redirect'])->name('google.auth');
    Route::get('auth/google/callback', [App\Http\Controllers\GoogleAuthController::class, 'callback'])->name('google.callback');
    Route::post('auth/google/disconnect', [App\Http\Controllers\GoogleAuthController::class, 'disconnect'])->name('google.disconnect');

    // レポート
    Volt::route('reports', 'reports.report-list')->name('reports.index');
    Route::prefix('reports')->name('reports.')->group(function () {
        Volt::route('generate', 'reports.generate-report')->name('generate');
        Volt::route('{id}', 'reports.report-detail')->name('show');
    });

    // インサイト
    Volt::route('insights', 'insights.insight-list')->name('insights.index');
    Route::prefix('insights')->name('insights.')->group(function () {
        Volt::route('{id}', 'insights.insight-detail')->name('show');
    });

    // 改善施策
    Volt::route('recommendations', 'recommendations.recommendation-list')->name('recommendations.index');
    Route::prefix('recommendations')->name('recommendations.')->group(function () {
        Volt::route('{id}', 'recommendations.recommendation-detail')->name('show');
    });

    // 設定
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});
