<?php

use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{state, computed, mount, on};

// 状態の定義
state([
    'selectedPeriod' => 'today',
    'metrics' => null,
    'loading' => false,
]);

// マウント時の処理
mount(function () {
    $this->loadMetrics();
});

// メトリクスデータの読み込み
$loadMetrics = function () {
    $this->loading = true;

    // TODO: 実際のデータ取得処理に置き換え
    // 現在はダミーデータ
    $this->metrics = [
        'impressions' => [
            'value' => 125000,
            'change' => 12.5,
            'trend' => 'up',
        ],
        'clicks' => [
            'value' => 3500,
            'change' => 8.3,
            'trend' => 'up',
        ],
        'cost' => [
            'value' => 85000,
            'change' => -5.2,
            'trend' => 'down',
        ],
        'conversions' => [
            'value' => 145,
            'change' => 15.8,
            'trend' => 'up',
        ],
        'ctr' => [
            'value' => 2.8,
            'change' => 0.3,
            'trend' => 'up',
        ],
        'cpa' => [
            'value' => 586,
            'change' => -12.1,
            'trend' => 'down',
        ],
    ];

    $this->loading = false;
};

// 期間変更
$changePeriod = function ($period) {
    $this->selectedPeriod = $period;
    $this->loadMetrics();
};

// データ更新
$refresh = function () {
    $this->loadMetrics();

    $this->dispatch('notify', [
        'message' => 'データを更新しました',
        'type' => 'success',
    ]);
};

?>

<div class="min-h-screen bg-gray-50 p-4 lg:p-8" x-data="{
    showStats: false,
    init() {
        setTimeout(() => { this.showStats = true }, 100)
    }
}">

    {{-- コンパクトなヘッダー --}}
    <div class="mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-[#0EA5E9] rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">ダッシュボード</h1>
                        <p class="text-xs text-gray-600">広告パフォーマンスをリアルタイムで確認</p>
                    </div>
                </div>

                <button wire:click="refresh" wire:loading.attr="disabled"
                    class="flex items-center gap-2 px-4 py-2 bg-[#0EA5E9] text-white rounded-lg hover:bg-[#0c8cc7] transition-colors disabled:opacity-50 shadow-sm text-sm">
                    <svg class="w-4 h-4" wire:loading.class="animate-spin" wire:target="refresh" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>更新</span>
                </button>
            </div>
        </div>
    </div>

    {{-- 期間選択 --}}
    <div class="mb-6 flex flex-wrap gap-2 bg-white p-2 rounded-xl shadow-sm border border-gray-200">
        <button wire:click="changePeriod('today')"
            class="flex-1 min-w-[100px] px-4 py-2.5 rounded-lg text-sm font-medium transition-colors {{ $selectedPeriod === 'today' ? 'bg-[#0EA5E9] text-white' : 'text-gray-700 hover:bg-gray-100' }}">
            今日
        </button>
        <button wire:click="changePeriod('yesterday')"
            class="flex-1 min-w-[100px] px-4 py-2.5 rounded-lg text-sm font-medium transition-colors {{ $selectedPeriod === 'yesterday' ? 'bg-[#0EA5E9] text-white' : 'text-gray-700 hover:bg-gray-100' }}">
            昨日
        </button>
        <button wire:click="changePeriod('week')"
            class="flex-1 min-w-[100px] px-4 py-2.5 rounded-lg text-sm font-medium transition-colors {{ $selectedPeriod === 'week' ? 'bg-[#0EA5E9] text-white' : 'text-gray-700 hover:bg-gray-100' }}">
            今週
        </button>
        <button wire:click="changePeriod('month')"
            class="flex-1 min-w-[100px] px-4 py-2.5 rounded-lg text-sm font-medium transition-colors {{ $selectedPeriod === 'month' ? 'bg-[#0EA5E9] text-white' : 'text-gray-700 hover:bg-gray-100' }}">
            今月
        </button>
    </div>

    {{-- ローディング状態 --}}
    <div wire:loading wire:target="loadMetrics" class="flex flex-col items-center justify-center py-20">
        <div class="relative w-24 h-24">
            <div class="absolute inset-0 border-4 border-blue-200 rounded-full"></div>
            <div class="absolute inset-0 border-4 border-transparent border-t-blue-600 rounded-full animate-spin"></div>
        </div>
        <p class="text-gray-600 font-semibold mt-6 text-lg">データを読み込んでいます...</p>
    </div>

    {{-- メトリクスカード --}}
    @if ($metrics && !$loading)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">

            {{-- インプレッション --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-[#0EA5E9]" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-600">インプレッション</p>
                        </div>
                        <p class="text-3xl font-bold text-gray-900 mb-2">
                            {{ number_format($metrics['impressions']['value']) }}
                        </p>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-green-50 rounded-md">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                            <span
                                class="text-sm font-semibold text-green-700">+{{ number_format($metrics['impressions']['change'], 1) }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- クリック数 --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-10 h-10 bg-purple-50 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-600">クリック数</p>
                        </div>
                        <p class="text-3xl font-bold text-gray-900 mb-2">
                            {{ number_format($metrics['clicks']['value']) }}
                        </p>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-green-50 rounded-md">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                            <span
                                class="text-sm font-semibold text-green-700">+{{ number_format($metrics['clicks']['change'], 1) }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- コンバージョン --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-600">コンバージョン</p>
                        </div>
                        <p class="text-3xl font-bold text-gray-900 mb-2">
                            {{ number_format($metrics['conversions']['value']) }}
                        </p>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-green-50 rounded-md">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                            <span
                                class="text-sm font-semibold text-green-700">+{{ number_format($metrics['conversions']['change'], 1) }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 費用 --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-10 h-10 bg-orange-50 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.05.402 2.75 1.015M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.05-.402-2.75-1.015M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-600">費用</p>
                        </div>
                        <p class="text-3xl font-bold text-gray-900 mb-2">
                            ¥{{ number_format($metrics['cost']['value']) }}
                        </p>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-green-50 rounded-md">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6" />
                            </svg>
                            <span
                                class="text-sm font-semibold text-green-700">{{ number_format($metrics['cost']['change'], 1) }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CTR --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-10 h-10 bg-indigo-50 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-600">CTR</p>
                        </div>
                        <p class="text-3xl font-bold text-gray-900 mb-2">
                            {{ number_format($metrics['ctr']['value'], 2) }}%
                        </p>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-green-50 rounded-md">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                            <span
                                class="text-sm font-semibold text-green-700">+{{ number_format($metrics['ctr']['change'], 1) }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CPA --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-10 h-10 bg-pink-50 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-600">CPA</p>
                        </div>
                        <p class="text-3xl font-bold text-gray-900 mb-2">
                            ¥{{ number_format($metrics['cpa']['value']) }}
                        </p>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-green-50 rounded-md">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6" />
                            </svg>
                            <span
                                class="text-sm font-semibold text-green-700">{{ number_format($metrics['cpa']['change'], 1) }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- クイックアクション --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-[#0EA5E9] rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">クイックアクション</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="/reports/generate"
                    class="group p-6 bg-blue-50 border border-blue-200 rounded-xl hover:bg-blue-100 transition-colors">
                    <div class="w-12 h-12 bg-[#0EA5E9] rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-gray-900 mb-1">AIレポート生成</h3>
                    <p class="text-sm text-gray-600">Geminiで効果分析</p>
                </a>

                <a href="/insights"
                    class="group p-6 bg-purple-50 border border-purple-200 rounded-xl hover:bg-purple-100 transition-colors">
                    <div class="w-12 h-12 bg-purple-600 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-gray-900 mb-1">インサイト確認</h3>
                    <p class="text-sm text-gray-600">データから洞察</p>
                </a>

                <a href="/recommendations"
                    class="group p-6 bg-green-50 border border-green-200 rounded-xl hover:bg-green-100 transition-colors">
                    <div class="w-12 h-12 bg-green-600 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-gray-900 mb-1">改善施策実施</h3>
                    <p class="text-sm text-gray-600">AI提案を確認</p>
                </a>
            </div>
        </div>
    @endif
</div>
