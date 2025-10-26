<?php

namespace App\Http\Controllers;

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
            return Socialite::driver('google')
                ->scopes([
                    'https://www.googleapis.com/auth/userinfo.email',
                    'https://www.googleapis.com/auth/userinfo.profile',
                ])
                ->redirect();
        } catch (\Exception $e) {
            Log::error('Google Auth redirect error: ' . $e->getMessage());

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
            $googleUser = Socialite::driver('google')->user();

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
                    'token_expires_at' => now()->addSeconds($googleUser->expiresIn),
                ]
            );

            return redirect('/accounts/google')
                ->with('message', 'Googleアカウントとの連携が完了しました！');
        } catch (\Exception $e) {
            Log::error('Google Auth callback error: ' . $e->getMessage());

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
