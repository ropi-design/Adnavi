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

// トレンド表示のヘルパー
$getTrendIcon = function ($trend) {
    return match ($trend) {
        'up' => 'arrow-trending-up',
        'down' => 'arrow-trending-down',
        default => 'minus',
    };
};

$getTrendColor = function ($trend, $inverse = false) {
    if ($inverse) {
        return match ($trend) {
            'up' => 'text-red-600',
            'down' => 'text-green-600',
            default => 'text-gray-600',
        };
    }

    return match ($trend) {
        'up' => 'text-green-600',
        'down' => 'text-red-600',
        default => 'text-gray-600',
    };
};

$getTrendBgColor = function ($trend, $inverse = false) {
    if ($inverse) {
        return match ($trend) {
            'up' => 'bg-red-50',
            'down' => 'bg-green-50',
            default => 'bg-gray-50',
        };
    }

    return match ($trend) {
        'up' => 'bg-green-50',
        'down' => 'bg-red-50',
        default => 'bg-gray-50',
    };
};

?>

<div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-indigo-50 space-y-8 p-6 lg:p-8 animate-fade-in">
    {{-- ヘッダー --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 mb-8">
        <div class="bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-xl border border-gray-200/50">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">ダッシュボード</h1>
            <p class="text-gray-600">広告効果の全体像を確認</p>
        </div>

        {{-- 期間選択 --}}
        <div class="flex gap-2 bg-white/90 backdrop-blur-sm p-1 rounded-xl shadow-lg border border-gray-200">
            <button wire:click="changePeriod('today')"
                class="px-4 py-2 rounded-md text-sm font-medium transition-all {{ $selectedPeriod === 'today' ? 'bg-blue-600 text-white shadow-md' : 'text-gray-600 hover:bg-gray-50' }}">
                今日
            </button>
            <button wire:click="changePeriod('yesterday')"
                class="px-4 py-2 rounded-md text-sm font-medium transition-all {{ $selectedPeriod === 'yesterday' ? 'bg-blue-600 text-white shadow-md' : 'text-gray-600 hover:bg-gray-50' }}">
                昨日
            </button>
            <button wire:click="changePeriod('week')"
                class="px-4 py-2 rounded-md text-sm font-medium transition-all {{ $selectedPeriod === 'week' ? 'bg-blue-600 text-white shadow-md' : 'text-gray-600 hover:bg-gray-50' }}">
                今週
            </button>
            <button wire:click="changePeriod('month')"
                class="px-4 py-2 rounded-md text-sm font-medium transition-all {{ $selectedPeriod === 'month' ? 'bg-blue-600 text-white shadow-md' : 'text-gray-600 hover:bg-gray-50' }}">
                今月
            </button>

            <button wire:click="refresh" wire:loading.attr="disabled"
                class="ml-2 px-4 py-2 text-gray-600 hover:bg-gray-50 rounded-md transition-colors disabled:opacity-50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    wire:loading.class="animate-spin" wire:target="refresh">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </button>
        </div>
    </div>

    {{-- ローディング状態 --}}
    <div wire:loading wire:target="loadMetrics" class="flex flex-col items-center justify-center py-16">
        <svg class="w-12 h-12 text-blue-600 animate-spin mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        <p class="text-gray-600 font-medium">データを読み込んでいます...</p>
    </div>

    {{-- メトリクスカード --}}
    @if ($metrics && !$loading)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- インプレッション --}}
            <div class="card p-6 bg-gradient-to-br from-blue-50 to-blue-100 border-blue-200">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-semibold text-blue-700 uppercase tracking-wide">インプレッション</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">
                            {{ number_format($metrics['impressions']['value']) }}</p>
                    </div>
                    <div class="p-3 bg-blue-200 rounded-xl">
                        <svg class="w-8 h-8 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </div>
                </div>
                <div
                    class="{{ $this->getTrendBgColor($metrics['impressions']['trend']) }} {{ $this->getTrendColor($metrics['impressions']['trend']) }} p-2 rounded-lg inline-flex items-center gap-2 text-sm font-semibold">
                    @if ($metrics['impressions']['trend'] === 'up')
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    @else
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6" />
                        </svg>
                    @endif
                    <span>+{{ number_format($metrics['impressions']['change'], 1) }}%</span>
                </div>
            </div>

            {{-- クリック数 --}}
            <div class="card p-6 bg-gradient-to-br from-purple-50 to-purple-100 border-purple-200">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-semibold text-purple-700 uppercase tracking-wide">クリック数</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">
                            {{ number_format($metrics['clicks']['value']) }}</p>
                    </div>
                    <div class="p-3 bg-purple-200 rounded-xl">
                        <svg class="w-8 h-8 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                        </svg>
                    </div>
                </div>
                <div
                    class="{{ $this->getTrendBgColor($metrics['clicks']['trend']) }} {{ $this->getTrendColor($metrics['clicks']['trend']) }} p-2 rounded-lg inline-flex items-center gap-2 text-sm font-semibold">
                    @if ($metrics['clicks']['trend'] === 'up')
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    @else
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6" />
                        </svg>
                    @endif
                    <span>+{{ number_format($metrics['clicks']['change'], 1) }}%</span>
                </div>
            </div>

            {{-- コンバージョン --}}
            <div class="card p-6 bg-gradient-to-br from-green-50 to-green-100 border-green-200">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-semibold text-green-700 uppercase tracking-wide">コンバージョン</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">
                            {{ number_format($metrics['conversions']['value']) }}</p>
                    </div>
                    <div class="p-3 bg-green-200 rounded-xl">
                        <svg class="w-8 h-8 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div
                    class="{{ $this->getTrendBgColor($metrics['conversions']['trend']) }} {{ $this->getTrendColor($metrics['conversions']['trend']) }} p-2 rounded-lg inline-flex items-center gap-2 text-sm font-semibold">
                    @if ($metrics['conversions']['trend'] === 'up')
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    @else
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6" />
                        </svg>
                    @endif
                    <span>+{{ number_format($metrics['conversions']['change'], 1) }}%</span>
                </div>
            </div>

            {{-- 費用 --}}
            <div class="card p-6 bg-gradient-to-br from-orange-50 to-orange-100 border-orange-200">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-semibold text-orange-700 uppercase tracking-wide">費用</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">
                            ¥{{ number_format($metrics['cost']['value']) }}</p>
                    </div>
                    <div class="p-3 bg-orange-200 rounded-xl">
                        <svg class="w-8 h-8 text-orange-700" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.05.402 2.75 1.015M10.97 4.969c.269-.27.576-.421.88-.525M16.75 9.75c.169-.368.206-.769.125-1.165M6.375 12.75a1.75 1.75 0 00-.75 1.5" />
                        </svg>
                    </div>
                </div>
                <div
                    class="{{ $this->getTrendBgColor($metrics['cost']['trend'], true) }} {{ $this->getTrendColor($metrics['cost']['trend'], true) }} p-2 rounded-lg inline-flex items-center gap-2 text-sm font-semibold">
                    @if ($metrics['cost']['trend'] === 'up')
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    @else
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6" />
                        </svg>
                    @endif
                    <span>{{ $metrics['cost']['change'] > 0 ? '+' : '' }}{{ number_format($metrics['cost']['change'], 1) }}%</span>
                </div>
            </div>

            {{-- CTR --}}
            <div class="card p-6 bg-gradient-to-br from-indigo-50 to-indigo-100 border-indigo-200">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-semibold text-indigo-700 uppercase tracking-wide">CTR</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">
                            {{ number_format($metrics['ctr']['value'], 2) }}%</p>
                    </div>
                    <div class="p-3 bg-indigo-200 rounded-xl">
                        <svg class="w-8 h-8 text-indigo-700" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
                <div
                    class="{{ $this->getTrendBgColor($metrics['ctr']['trend']) }} {{ $this->getTrendColor($metrics['ctr']['trend']) }} p-2 rounded-lg inline-flex items-center gap-2 text-sm font-semibold">
                    @if ($metrics['ctr']['trend'] === 'up')
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    @else
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6 6" />
                        </svg>
                    @endif
                    <span>+{{ number_format($metrics['ctr']['change'], 1) }}%</span>
                </div>
            </div>

            {{-- CPA --}}
            <div class="card p-6 bg-gradient-to-br from-red-50 to-red-100 border-red-200">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-semibold text-red-700 uppercase tracking-wide">CPA</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">
                            ¥{{ number_format($metrics['cpa']['value']) }}</p>
                    </div>
                    <div class="p-3 bg-red-200 rounded-xl">
                        <svg class="w-8 h-8 text-red-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <div
                    class="{{ $this->getTrendBgColor($metrics['cpa']['trend'], true) }} {{ $this->getTrendColor($metrics['cpa']['trend'], true) }} p-2 rounded-lg inline-flex items-center gap-2 text-sm font-semibold">
                    @if ($metrics['cpa']['trend'] === 'up')
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8-8-4 4-6 6" />
                        </svg>
                    @else
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6 6" />
                        </svg>
                    @endif
                    <span>{{ $metrics['cpa']['change'] > 0 ? '+' : '' }}{{ number_format($metrics['cpa']['change'], 1) }}%</span>
                </div>
            </div>
        </div>

        {{-- クイックアクション --}}
        <div class="card p-8">
            <div class="flex items-center gap-3 mb-6">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <h2 class="text-2xl font-bold text-gray-900">クイックアクション</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="/reports/generate"
                    class="p-6 bg-gradient-to-br from-blue-50 to-blue-100 border-2 border-blue-200 rounded-xl hover:shadow-lg transition-all group">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-blue-200 rounded-lg group-hover:bg-blue-300 transition-colors">
                            <svg class="w-8 h-8 text-blue-700" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-gray-900">AIレポート生成</h3>
                            <p class="text-sm text-gray-600 mt-1">Geminiで効果分析</p>
                        </div>
                    </div>
                </a>

                <a href="/insights"
                    class="p-6 bg-gradient-to-br from-purple-50 to-purple-100 border-2 border-purple-200 rounded-xl hover:shadow-lg transition-all group">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-purple-200 rounded-lg group-hover:bg-purple-300 transition-colors">
                            <svg class="w-8 h-8 text-purple-700" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M6.343 6.343l-.707.707m12.728 0l-.707.707M6.343 17.657l-.707.707M17.657 6.343l-.707-.707m-12.728 0l-.707.707m12.728 12.728l-.707.707M17.657 17.657l-.707-.707M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-gray-900">インサイトを見る</h3>
                            <p class="text-sm text-gray-600 mt-1">データから洞察</p>
                        </div>
                    </div>
                </a>

                <a href="/recommendations"
                    class="p-6 bg-gradient-to-br from-green-50 to-green-100 border-2 border-green-200 rounded-xl hover:shadow-lg transition-all group">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-green-200 rounded-lg group-hover:bg-green-300 transition-colors">
                            <svg class="w-8 h-8 text-green-700" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-gray-900">改善施策を確認</h3>
                            <p class="text-sm text-gray-600 mt-1">AI提案を実施</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    @endif
</div>
