<?php

use App\Models\AdAccount;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{state, mount};

state(['adAccounts' => []]);

mount(function () {
    $this->adAccounts = AdAccount::where('user_id', Auth::id())->where('is_active', true)->with('googleAccount')->latest()->get();
});

$checkSync = function ($accountId) {
    $account = AdAccount::find($accountId);
    if ($account && $account->needsSync()) {
        session()->flash('message', 'アカウント ' . $account->account_name . ' は同期が必要です');
    } else {
        session()->flash('message', 'アカウント ' . $account->account_name . ' は最新です');
    }
};

?>

<div class="space-y-6 p-6 lg:p-8">
    {{-- ヘッダー --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold" style="color: #ffffff;">広告アカウント</h1>
            <p class="mt-1" style="color: #ffffff;">連携中の広告アカウントを管理</p>
        </div>
        <a href="/accounts/google" class="btn btn-primary inline-flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
            </svg>
            Google連携
        </a>
    </div>

    {{-- メッセージ --}}
    @if (session('message'))
        <div class="p-4 bg-blue-100 border-l-4 border-blue-500 rounded-lg shadow-sm">
            <div class="flex items-center gap-2 text-blue-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ session('message') }}
            </div>
        </div>
    @endif

    {{-- アカウントリスト --}}
    @if ($adAccounts->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($adAccounts as $account)
                <div class="card p-6 hover:shadow-xl transition-all duration-300 group">
                    <div class="space-y-4">
                        {{-- ヘッダー --}}
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="p-2 bg-blue-100 rounded-lg group-hover:bg-blue-200 transition-colors">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-bold text-lg truncate" style="color: #ffffff;">
                                            {{ $account->account_name }}</h3>
                                        <p class="text-sm font-mono" style="color: #9ca3af;">ID:
                                            {{ $account->customer_id }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 情報 --}}
                        <div class="space-y-3 rounded-lg p-4" style="background-color: #ffffff;">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.05.402 2.75 1.015L18 5.5c.05-.375.125-.745.125-1.125C18.125 2.57 16.554 1 14.625 1S11.25 2.57 11.25 4.375c0 .75.25 1.5.625 2.125L9.75 8.625c-.75-.613-1.593-1.015-2.625-1.015S5.25 7.52 5.25 9.25s1.343 2 3 2 3 .895 3 2-1.343 2-3 2" />
                                    </svg>
                                    通貨
                                </span>
                                <span class="font-bold text-gray-900">{{ $account->currency }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    タイムゾーン
                                </span>
                                <span class="font-bold text-gray-900">{{ $account->timezone }}</span>
                            </div>
                            @if ($account->last_synced_at)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600 flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        最終同期
                                    </span>
                                    <span
                                        class="font-semibold text-blue-600">{{ $account->last_synced_at->diffForHumans() }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- アクション --}}
                        <div class="flex gap-2 pt-2 border-t border-gray-200">
                            <button wire:click="checkSync({{ $account->id }})"
                                class="flex-1 px-4 py-2.5 rounded-lg hover:bg-gray-50 transition-colors font-medium text-sm flex items-center justify-center gap-2"
                                style="background-color: #ffffff; color: #000000;">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                同期確認
                            </button>
                            <button
                                class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-sm">
                                詳細
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card">
            <div class="text-center py-16 text-gray-500">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-6">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">広告アカウントが登録されていません</h3>
                <p class="text-gray-500 mb-6">Googleアカウントと連携して始めましょう</p>
                <a href="/accounts/google" class="btn btn-primary inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                    Googleアカウントと連携
                </a>
            </div>
        </div>
    @endif
</div>
