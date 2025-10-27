<?php

use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Features;
use function Livewire\Volt\{state, mount};

state([
    'twoFactorEnabled' => false,
    'qrCodeSvg' => '',
    'showQrCode' => false,
    'recoveryCodes' => [],
]);

mount(function () {
    abort_unless(Features::enabled(Features::twoFactorAuthentication()), 403);

    $this->twoFactorEnabled = auth()->user()->hasEnabledTwoFactorAuthentication();
});

$enable = function (EnableTwoFactorAuthentication $enableTwoFactorAuthentication) {
    $enableTwoFactorAuthentication(auth()->user());

    $user = auth()->user()->fresh();
    $this->twoFactorEnabled = true;
    $this->qrCodeSvg = $user->twoFactorQrCodeSvg();
    $this->recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
    $this->showQrCode = true;

    session()->flash('message', '2要素認証を有効にしました');
};

$disable = function (DisableTwoFactorAuthentication $disableTwoFactorAuthentication) {
    $disableTwoFactorAuthentication(auth()->user());

    $this->twoFactorEnabled = false;
    $this->showQrCode = false;

    session()->flash('message', '2要素認証を無効にしました');
};

$hideQrCode = function () {
    $this->showQrCode = false;
};

?>

<div class="p-6 lg:p-8 space-y-8 animate-fade-in">
    <div class="max-w-3xl mx-auto">
        {{-- ヘッダー --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">2要素認証</h1>
            <p class="text-gray-600 mt-2">アカウントのセキュリティを強化します</p>
        </div>

        {{-- メッセージ --}}
        @if (session('message'))
            <div class="p-4 bg-green-100 border-l-4 border-green-500 rounded-lg mb-6">
                <div class="flex items-center gap-2 text-green-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ session('message') }}
                </div>
            </div>
        @endif

        {{-- 2要素認証設定 --}}
        <div class="card p-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">2要素認証 (2FA)</h2>
                    <p class="text-sm text-gray-600 mt-1">
                        @if ($twoFactorEnabled)
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                有効
                            </span>
                        @else
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-800">
                                無効
                            </span>
                        @endif
                    </p>
                </div>

                <div>
                    @if ($twoFactorEnabled)
                        <button wire:click="disable" class="btn btn-danger">
                            無効にする
                        </button>
                    @else
                        <button wire:click="enable" class="btn btn-primary">
                            有効にする
                        </button>
                    @endif
                </div>
            </div>

            <div class="space-y-4">
                <p class="text-gray-700">
                    2要素認証を有効にすると、ログイン時にパスワードに加えて、認証アプリから取得した6桁のコードの入力が必要になります。
                </p>

                @if ($showQrCode)
                    <div class="mt-6 p-6 bg-blue-50 border border-blue-200 rounded-xl">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">QRコードをスキャン</h3>
                        <p class="text-sm text-gray-700 mb-4">
                            Google AuthenticatorやAuthyなどの認証アプリでこのQRコードをスキャンしてください。
                        </p>

                        <div class="flex justify-center mb-6 bg-white p-4 rounded-lg">
                            {!! $qrCodeSvg !!}
                        </div>

                        @if (count($recoveryCodes) > 0)
                            <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <h4 class="text-sm font-bold text-gray-900 mb-3">リカバリーコード</h4>
                                <p class="text-xs text-gray-700 mb-3">
                                    認証アプリにアクセスできなくなった場合に使用できます。安全な場所に保存してください。
                                </p>
                                <div class="grid grid-cols-2 gap-2 text-sm font-mono bg-white p-3 rounded">
                                    @foreach ($recoveryCodes as $code)
                                        <div class="text-gray-800">{{ $code }}</div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <button wire:click="hideQrCode" class="mt-4 btn btn-secondary w-full">
                            閉じる
                        </button>
                    </div>
                @endif
            </div>
        </div>

        {{-- セキュリティ情報 --}}
        <div class="card p-6 bg-blue-50 border-blue-200">
            <h3 class="text-lg font-bold text-gray-900 mb-3">2要素認証について</h3>
            <ul class="space-y-2 text-sm text-gray-700">
                <li class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span>アカウントのセキュリティを大幅に向上させます</span>
                </li>
                <li class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span>Google Authenticator、Authy、1Password等の認証アプリが必要です</span>
                </li>
                <li class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    <span>リカバリーコードは必ず安全な場所に保管してください</span>
                </li>
            </ul>
        </div>
    </div>
</div>
