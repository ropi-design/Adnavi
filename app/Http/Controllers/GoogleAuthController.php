<?php

namespace App\Http\Controllers;

use App\Services\Google\GoogleAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Google認証へのリダイレクト
     */
    public function redirect()
    {
        try {
            // リクエストのベースURLを使用してリダイレクトURIを動的に生成
            $redirectUrl = request()->getSchemeAndHttpHost() . '/auth/google/callback';

            Log::info('Google Auth redirect', [
                'redirect_url' => $redirectUrl,
                'request_url' => request()->fullUrl(),
                'config_redirect' => config('services.google.redirect'),
            ]);

            // 設定を一時的に上書きしてリダイレクトURIを設定
            config(['services.google.redirect' => $redirectUrl]);

            return Socialite::driver('google')
                ->scopes([
                    'https://www.googleapis.com/auth/userinfo.email',
                    'https://www.googleapis.com/auth/userinfo.profile',
                    'https://www.googleapis.com/auth/adwords',
                    'https://www.googleapis.com/auth/analytics.readonly',
                    'https://www.googleapis.com/auth/analytics',
                ])
                ->redirect();
        } catch (\Exception $e) {
            Log::error('Google Auth redirect error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect('/accounts/google')
                ->with('error', 'Google認証の開始に失敗しました: ' . $e->getMessage());
        }
    }

    /**
     * Google認証のコールバック
     */
    public function callback()
    {
        try {
            // リダイレクトURIを設定（コールバック時も必要）
            $redirectUrl = request()->getSchemeAndHttpHost() . '/auth/google/callback';
            config(['services.google.redirect' => $redirectUrl]);

            Log::info('Google Auth callback started', [
                'request_url' => request()->fullUrl(),
                'redirect_url' => $redirectUrl,
                'user_id' => Auth::id(),
            ]);

            // ユーザーがログインしているか確認
            if (!Auth::check()) {
                Log::warning('User not authenticated in Google callback');
                return redirect('/login')
                    ->with('error', 'ログインが必要です');
            }

            $googleUser = Socialite::driver('google')->user();
            Log::info('Google user retrieved', ['email' => $googleUser->getEmail()]);

            $user = Auth::user();

            // GoogleAccountモデルで保存
            $googleAccount = $user->googleAccounts()->updateOrCreate(
                [
                    'google_id' => $googleUser->getId(),
                ],
                [
                    'email' => $googleUser->getEmail(),
                    'access_token' => encrypt($googleUser->token),
                    'refresh_token' => encrypt($googleUser->refreshToken ?? ''),
                    'token_expires_at' => now()->addSeconds($googleUser->expiresIn ?? 3600),
                ]
            );

            Log::info('Google account saved', [
                'google_account_id' => $googleAccount->id,
                'user_id' => $user->id,
            ]);

            // Analyticsプロパティを取得して保存（環境変数で有効/無効を制御可能）
            // 注意: 同期処理が長引く可能性があるため、エラーが発生しても処理を継続
            if (env('SYNC_ANALYTICS_PROPERTIES_ON_CONNECT', true)) {
                try {
                    Log::info('Starting Analytics properties sync');

                    // タイムアウトを設定して、長時間実行されないようにする
                    set_time_limit(30);

                    $analyticsService = new GoogleAnalyticsService();
                    $analyticsService->initialize($googleAccount);
                    $savedCount = $analyticsService->syncPropertiesToDatabase();

                    if ($savedCount > 0) {
                        Log::info("Synced {$savedCount} Analytics properties for user {$user->id}");
                    } else {
                        Log::info("No Analytics properties found or all already synced for user {$user->id}");
                    }
                } catch (\Throwable $e) {
                    // Analyticsプロパティの取得に失敗しても連携自体は成功とする
                    Log::warning('Failed to sync Analytics properties after Google account connection', [
                        'user_id' => $user->id,
                        'google_account_id' => $googleAccount->id,
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    // エラーをユーザーに表示しない（静かに失敗する）
                    // Analyticsプロパティの同期は後で手動で実行可能
                }
            } else {
                Log::info('Analytics properties sync skipped (SYNC_ANALYTICS_PROPERTIES_ON_CONNECT=false)');
            }

            Log::info('Google Auth callback completed successfully');

            return redirect('/accounts/google')
                ->with('message', 'Googleアカウントとの連携が完了しました！');
        } catch (\Illuminate\Auth\AuthenticationException $e) {
            Log::error('Authentication error in Google callback', [
                'error' => $e->getMessage(),
            ]);
            return redirect('/login')
                ->with('error', 'ログインが必要です');
        } catch (\Exception $e) {
            Log::error('Google Auth callback error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect('/accounts/google')
                ->with('error', 'Google認証に失敗しました: ' . $e->getMessage());
        }
    }

    /**
     * Googleアカウントの連携解除
     */
    public function disconnect()
    {
        try {
            Auth::user()->googleAccounts()->delete();

            return redirect('/accounts/google')
                ->with('message', 'Googleアカウントの連携を解除しました');
        } catch (\Exception $e) {
            Log::error('Google Auth disconnect error: ' . $e->getMessage());

            return redirect('/accounts/google')
                ->with('error', '連携解除に失敗しました: ' . $e->getMessage());
        }
    }
}
