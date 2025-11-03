<?php

use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{state, computed, mount, on};

// 状態の定義
state([
    'selectedPeriod' => 'today',
    'metrics' => null,
    'loading' => false,
    'customStartDate' => null,
    'customEndDate' => null,
    'showCustomDatePicker' => false,
    'dateRange' => null,
    'showGraphModal' => false,
    'selectedMetric' => null,
    'dailyData' => null,
]);

// マウント時の処理
mount(function () {
    $this->loadMetrics();
});

// カスタム期間の日数を計算
$calculateCustomDays = function () {
    if (!$this->customStartDate || !$this->customEndDate) {
        return 7; // デフォルトは7日
    }

    $start = \Carbon\Carbon::parse($this->customStartDate);
    $end = \Carbon\Carbon::parse($this->customEndDate);

    return max(1, $start->diffInDays($end) + 1);
};

// 日付範囲を計算
$calculateDateRange = function () {
    $now = \Carbon\Carbon::now();

    return match ($this->selectedPeriod) {
        'today' => [
            'start' => $now->copy()->startOfDay(),
            'end' => $now->copy()->endOfDay(),
        ],
        'week' => [
            'start' => $now->copy()->startOfWeek(),
            'end' => $now->copy()->endOfWeek(),
        ],
        'month' => [
            'start' => $now->copy()->startOfMonth(),
            'end' => $now->copy()->endOfMonth(),
        ],
        'custom' => [
            'start' => $this->customStartDate ? \Carbon\Carbon::parse($this->customStartDate)->startOfDay() : $now->copy()->startOfDay(),
            'end' => $this->customEndDate ? \Carbon\Carbon::parse($this->customEndDate)->endOfDay() : $now->copy()->endOfDay(),
        ],
        default => [
            'start' => $now->copy()->startOfDay(),
            'end' => $now->copy()->endOfDay(),
        ],
    };
};

// メトリクスデータの読み込み
$loadMetrics = function () {
    $this->loading = true;

    // 日付範囲を計算して保存
    $this->dateRange = $this->calculateDateRange();
    $dateRange = $this->dateRange;
    $startDate = $dateRange['start'];
    $endDate = $dateRange['end'];

    // ユーザーの広告アカウントを取得
    $user = Auth::user();
    $adAccounts = \App\Models\AdAccount::where('user_id', $user->id)->where('is_active', true)->pluck('id');

    if ($adAccounts->isEmpty()) {
        // データがない場合は0を返す
        $this->metrics = [
            'impressions' => ['value' => 0, 'change' => 0, 'trend' => 'neutral'],
            'clicks' => ['value' => 0, 'change' => 0, 'trend' => 'neutral'],
            'cost' => ['value' => 0, 'change' => 0, 'trend' => 'neutral'],
            'conversions' => ['value' => 0, 'change' => 0, 'trend' => 'neutral'],
            'ctr' => ['value' => 0, 'change' => 0, 'trend' => 'neutral'],
            'cpa' => ['value' => 0, 'change' => 0, 'trend' => 'neutral'],
            'cpc' => ['value' => 0, 'change' => 0, 'trend' => 'neutral'],
            'cvr' => ['value' => 0, 'change' => 0, 'trend' => 'neutral'],
        ];
        $this->loading = false;
        return;
    }

    // キャンペーンIDを取得
    $campaignIds = \App\Models\Campaign::whereIn('ad_account_id', $adAccounts)->pluck('id');

    if ($campaignIds->isEmpty()) {
        $this->metrics = [
            'impressions' => ['value' => 0, 'change' => 0, 'trend' => 'neutral'],
            'clicks' => ['value' => 0, 'change' => 0, 'trend' => 'neutral'],
            'cost' => ['value' => 0, 'change' => 0, 'trend' => 'neutral'],
            'conversions' => ['value' => 0, 'change' => 0, 'trend' => 'neutral'],
            'ctr' => ['value' => 0, 'change' => 0, 'trend' => 'neutral'],
            'cpa' => ['value' => 0, 'change' => 0, 'trend' => 'neutral'],
            'cpc' => ['value' => 0, 'change' => 0, 'trend' => 'neutral'],
            'cvr' => ['value' => 0, 'change' => 0, 'trend' => 'neutral'],
        ];
        $this->loading = false;
        return;
    }

    // 現在期間のデータを取得
    $currentMetrics = \Illuminate\Support\Facades\DB::table('ad_metrics_daily')
        ->whereIn('campaign_id', $campaignIds)
        ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
        ->select(\Illuminate\Support\Facades\DB::raw('SUM(impressions) as total_impressions'), \Illuminate\Support\Facades\DB::raw('SUM(clicks) as total_clicks'), \Illuminate\Support\Facades\DB::raw('SUM(cost) as total_cost'), \Illuminate\Support\Facades\DB::raw('SUM(conversions) as total_conversions'), \Illuminate\Support\Facades\DB::raw('AVG(ctr) as avg_ctr'), \Illuminate\Support\Facades\DB::raw('AVG(cpc) as avg_cpc'), \Illuminate\Support\Facades\DB::raw('AVG(cpa) as avg_cpa'))
        ->first();

    $currentImpressions = (int) ($currentMetrics->total_impressions ?? 0);
    $currentClicks = (int) ($currentMetrics->total_clicks ?? 0);
    $currentCost = (float) ($currentMetrics->total_cost ?? 0);
    $currentConversions = (float) ($currentMetrics->total_conversions ?? 0);
    $currentCtr = (float) ($currentMetrics->avg_ctr ?? 0);
    $currentCpc = (float) ($currentMetrics->avg_cpc ?? 0);
    $currentCpa = (float) ($currentMetrics->avg_cpa ?? 0);
    $currentCvr = $currentClicks > 0 ? ($currentConversions / $currentClicks) * 100 : 0;

    // 前期間のデータを取得（比較用）
    $periodDays = $startDate->diffInDays($endDate) + 1;
    $previousStartDate = $startDate->copy()->subDays($periodDays);
    $previousEndDate = $startDate->copy()->subDay();

    $previousMetrics = \Illuminate\Support\Facades\DB::table('ad_metrics_daily')
        ->whereIn('campaign_id', $campaignIds)
        ->whereBetween('date', [$previousStartDate->format('Y-m-d'), $previousEndDate->format('Y-m-d')])
        ->select(\Illuminate\Support\Facades\DB::raw('SUM(impressions) as total_impressions'), \Illuminate\Support\Facades\DB::raw('SUM(clicks) as total_clicks'), \Illuminate\Support\Facades\DB::raw('SUM(cost) as total_cost'), \Illuminate\Support\Facades\DB::raw('SUM(conversions) as total_conversions'), \Illuminate\Support\Facades\DB::raw('AVG(ctr) as avg_ctr'), \Illuminate\Support\Facades\DB::raw('AVG(cpc) as avg_cpc'), \Illuminate\Support\Facades\DB::raw('AVG(cpa) as avg_cpa'))
        ->first();

    $previousImpressions = (int) ($previousMetrics->total_impressions ?? 0);
    $previousClicks = (int) ($previousMetrics->total_clicks ?? 0);
    $previousCost = (float) ($previousMetrics->total_cost ?? 0);
    $previousConversions = (float) ($previousMetrics->total_conversions ?? 0);
    $previousCtr = (float) ($previousMetrics->avg_ctr ?? 0);
    $previousCpc = (float) ($previousMetrics->avg_cpc ?? 0);
    $previousCpa = (float) ($previousMetrics->avg_cpa ?? 0);
    $previousCvr = $previousClicks > 0 ? ($previousConversions / $previousClicks) * 100 : 0;

    // 変化率を計算
    $calculateChange = function ($current, $previous) {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        return (($current - $previous) / $previous) * 100;
    };

    $impressionsChange = $calculateChange($currentImpressions, $previousImpressions);
    $clicksChange = $calculateChange($currentClicks, $previousClicks);
    $costChange = $calculateChange($currentCost, $previousCost);
    $conversionsChange = $calculateChange($currentConversions, $previousConversions);
    $ctrChange = $previousCtr > 0 ? (($currentCtr - $previousCtr) / $previousCtr) * 100 : 0;
    $cpcChange = $previousCpc > 0 ? (($currentCpc - $previousCpc) / $previousCpc) * 100 : 0;
    $cpaChange = $previousCpa > 0 ? (($currentCpa - $previousCpa) / $previousCpa) * 100 : 0;
    $cvrChange = $previousCvr > 0 ? (($currentCvr - $previousCvr) / $previousCvr) * 100 : 0;

    // トレンド判定
    $getTrend = function ($change) {
        if ($change > 0) {
            return 'up';
        }
        if ($change < 0) {
            return 'down';
        }
        return 'neutral';
    };

    $this->metrics = [
        'impressions' => [
            'value' => $currentImpressions,
            'change' => round($impressionsChange, 1),
            'trend' => $getTrend($impressionsChange),
        ],
        'clicks' => [
            'value' => $currentClicks,
            'change' => round($clicksChange, 1),
            'trend' => $getTrend($clicksChange),
        ],
        'cost' => [
            'value' => (int) $currentCost,
            'change' => round($costChange, 1),
            'trend' => $getTrend($costChange),
        ],
        'conversions' => [
            'value' => (int) $currentConversions,
            'change' => round($conversionsChange, 1),
            'trend' => $getTrend($conversionsChange),
        ],
        'ctr' => [
            'value' => round($currentCtr, 2),
            'change' => round($ctrChange, 1),
            'trend' => $getTrend($ctrChange),
        ],
        'cpa' => [
            'value' => (int) $currentCpa,
            'change' => round($cpaChange, 1),
            'trend' => $getTrend($cpaChange),
        ],
        'cpc' => [
            'value' => round($currentCpc, 2),
            'change' => round($cpcChange, 1),
            'trend' => $getTrend($cpcChange),
        ],
        'cvr' => [
            'value' => round($currentCvr, 2),
            'change' => round($cvrChange, 1),
            'trend' => $getTrend($cvrChange),
        ],
    ];

    $this->loading = false;
};

// 期間変更
$changePeriod = function ($period) {
    $this->selectedPeriod = $period;
    if ($period !== 'custom') {
        $this->customStartDate = null;
        $this->customEndDate = null;
        $this->showCustomDatePicker = false;
    }
    $this->loadMetrics();
};

// カスタム期間設定
$setCustomDate = function () {
    if ($this->customStartDate && $this->customEndDate) {
        $this->selectedPeriod = 'custom';
        $this->loadMetrics();
    }
};

// データ更新
$refresh = function () {
    $this->loadMetrics();

    $this->dispatch('notify', [
        'message' => 'データを更新しました',
        'type' => 'success',
    ]);
};

// メトリクス名を取得
$getMetricName = function ($metricKey) {
    $metricNames = [
        'impressions' => 'インプレッション',
        'clicks' => 'クリック数',
        'cost' => '費用',
        'conversions' => 'コンバージョン',
        'ctr' => 'クリック率',
        'cpc' => 'クリック単価',
        'cpa' => '獲得単価',
        'cvr' => 'コンバージョン率',
    ];
    return $metricNames[$metricKey] ?? $metricKey;
};

// メトリクスのSELECT文を生成
$getMetricSelectRaw = function ($metricKey) {
    $selectRaw = match ($metricKey) {
        'impressions' => 'SUM(impressions) as value',
        'clicks' => 'SUM(clicks) as value',
        'cost' => 'SUM(cost) as value',
        'conversions' => 'SUM(conversions) as value',
        'ctr' => 'AVG(ctr) as value',
        'cpc' => 'AVG(cpc) as value',
        'cpa' => 'AVG(cpa) as value',
        'cvr' => 'CASE WHEN SUM(clicks) > 0 THEN (SUM(conversions) / SUM(clicks) * 100) ELSE 0 END as value',
        default => '0 as value',
    };
    return $selectRaw;
};

// 日別データを生成
$generateDailyData = function ($metricKey) use ($getMetricName, $getMetricSelectRaw) {
    $dateRange = $this->calculateDateRange();
    $start = $dateRange['start'];
    $end = $dateRange['end'];

    // ユーザーの広告アカウントを取得
    $user = Auth::user();
    $adAccounts = \App\Models\AdAccount::where('user_id', $user->id)->where('is_active', true)->pluck('id');

    if ($adAccounts->isEmpty()) {
        return [
            'dates' => [],
            'values' => [],
            'name' => $getMetricName($metricKey),
            'key' => $metricKey,
        ];
    }

    // キャンペーンIDを取得
    $campaignIds = \App\Models\Campaign::whereIn('ad_account_id', $adAccounts)->pluck('id');

    if ($campaignIds->isEmpty()) {
        return [
            'dates' => [],
            'values' => [],
            'name' => $getMetricName($metricKey),
            'key' => $metricKey,
        ];
    }

    // 日別データを取得
    $dailyData = \Illuminate\Support\Facades\DB::table('ad_metrics_daily')
        ->whereIn('campaign_id', $campaignIds)
        ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
        ->select('date')
        ->selectRaw($getMetricSelectRaw($metricKey))
        ->groupBy('date')
        ->orderBy('date')
        ->get();

    $dates = [];
    $data = [];

    foreach ($dailyData as $row) {
        $dates[] = $row->date;
        $data[] = (float) ($row->value ?? 0);
    }

    // データがない場合は空の配列を返す
    if (empty($dates)) {
        $days = $start->diffInDays($end) + 1;
        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i);
            $dates[] = $date->format('Y-m-d');
            $data[] = 0;
        }
    }

    return [
        'dates' => $dates,
        'values' => $data,
        'name' => $getMetricName($metricKey),
        'key' => $metricKey,
    ];
};

// メトリクスクリック時の処理
$showMetricGraph = function ($metricKey) {
    $this->selectedMetric = $metricKey;
    $this->dailyData = $this->generateDailyData($metricKey);
    $this->showGraphModal = true;
};

// グラフモーダルを閉じる
$closeGraphModal = function () {
    $this->showGraphModal = false;
    $this->selectedMetric = null;
    $this->dailyData = null;
};

?>

<div class="min-h-screen bg-black p-4 lg:p-8" x-data="{
    showStats: false,
    init() {
        setTimeout(() => { this.showStats = true }, 100)
    }
}">

    {{-- コンパクトなヘッダー --}}
    <div class="mb-6">
        <div class="bg-zinc-900 rounded-xl shadow-sm border border-zinc-700 p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background-color: #4285F4;">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white">Google広告ダッシュボード</h1>
                        <p class="text-xs text-gray-400">広告パフォーマンスをリアルタイムで確認</p>
                        @if ($dateRange)
                            <p class="text-xs text-gray-500 mt-1">
                                期間: {{ $dateRange['start']->format('Y年m月d日') }} ～
                                {{ $dateRange['end']->format('Y年m月d日') }}
                            </p>
                        @endif
                    </div>
                </div>

                <button wire:click="refresh" wire:loading.attr="disabled"
                    class="flex items-center gap-2 px-4 py-2 text-white rounded-lg transition-colors disabled:opacity-50 shadow-sm text-sm hover:opacity-90"
                    style="background-color: #4285F4;">
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
    <div class="mb-6 bg-zinc-900 p-4 rounded-xl shadow-sm border border-zinc-700">
        <div class="flex gap-2 mb-3">
            <button wire:click="changePeriod('today')" type="button"
                class="flex-1 px-4 py-2.5 rounded-lg text-sm font-medium whitespace-nowrap {{ $selectedPeriod === 'today' ? 'text-white' : 'text-gray-300 hover:bg-zinc-800' }}"
                style="{{ $selectedPeriod === 'today' ? 'background-color: #4285F4;' : '' }}">
                今日
            </button>
            <button wire:click="changePeriod('week')" type="button"
                class="flex-1 px-4 py-2.5 rounded-lg text-sm font-medium whitespace-nowrap {{ $selectedPeriod === 'week' ? 'text-white' : 'text-gray-300 hover:bg-zinc-800' }}"
                style="{{ $selectedPeriod === 'week' ? 'background-color: #4285F4;' : '' }}">
                今週
            </button>
            <button wire:click="changePeriod('month')" type="button"
                class="flex-1 px-4 py-2.5 rounded-lg text-sm font-medium whitespace-nowrap {{ $selectedPeriod === 'month' ? 'text-white' : 'text-gray-300 hover:bg-zinc-800' }}"
                style="{{ $selectedPeriod === 'month' ? 'background-color: #4285F4;' : '' }}">
                今月
            </button>
            <button wire:click="$set('showCustomDatePicker', true)" type="button"
                class="flex-1 px-4 py-2.5 rounded-lg text-sm font-medium whitespace-nowrap {{ $selectedPeriod === 'custom' ? 'text-white' : 'text-gray-300 hover:bg-zinc-800' }}"
                style="{{ $selectedPeriod === 'custom' ? 'background-color: #4285F4;' : '' }}">
                カスタム期間
            </button>
        </div>

        @if ($showCustomDatePicker || $selectedPeriod === 'custom')
            <div class="mt-4 pt-4 border-t border-zinc-700 flex gap-3 items-end" x-data="{
                init() {
                    setTimeout(() => {
                        const startInput = document.getElementById('start-date-picker');
                        const endInput = document.getElementById('end-date-picker');
            
                        if (startInput && !startInput._flatpickr) {
                            flatpickr(startInput, {
                                dateFormat: 'Y-m-d',
                                locale: flatpickr.l10ns.ja,
                                theme: 'dark',
                                onChange: function(selectedDates, dateStr) {
                                    @this.set('customStartDate', dateStr);
                                    if (endInput && endInput._flatpickr) {
                                        endInput._flatpickr.set('minDate', dateStr);
                                    }
                                }
                            });
                        }
            
                        if (endInput && !endInput._flatpickr) {
                            flatpickr(endInput, {
                                dateFormat: 'Y-m-d',
                                locale: flatpickr.l10ns.ja,
                                theme: 'dark',
                                minDate: startInput && startInput.value ? startInput.value : null,
                                onChange: function(selectedDates, dateStr) {
                                    @this.set('customEndDate', dateStr);
                                }
                            });
                        }
                    }, 100);
                }
            }">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-400 mb-2">開始日</label>
                    <input type="text" id="start-date-picker" wire:model="customStartDate" placeholder="開始日を選択"
                        class="w-full px-3 py-2 bg-zinc-800 border border-zinc-700 rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        readonly>
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-400 mb-2">終了日</label>
                    <input type="text" id="end-date-picker" wire:model="customEndDate" placeholder="終了日を選択"
                        class="w-full px-3 py-2 bg-zinc-800 border border-zinc-700 rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        readonly>
                </div>
                <div class="flex flex-col gap-2">
                    <button wire:click="setCustomDate" wire:loading.attr="disabled"
                        @if (!$customStartDate || !$customEndDate) disabled @endif
                        class="px-4 py-2 text-sm text-white rounded-lg transition-colors disabled:opacity-50 whitespace-nowrap hover:opacity-90"
                        style="background-color: #4285F4;">
                        <span wire:loading.remove wire:target="setCustomDate">確定</span>
                        <span wire:loading wire:target="setCustomDate">読み込み中...</span>
                    </button>
                    <button wire:click="$set('showCustomDatePicker', false)" type="button"
                        class="px-4 py-2 text-sm text-gray-300 hover:text-white whitespace-nowrap">
                        閉じる
                    </button>
                </div>
            </div>
        @endif
    </div>

    {{-- メトリクスカード --}}
    {{-- 
        レイアウト構造:
        - 各広告プラットフォームごとに列を割り当て
        - 現在: Google広告のみ（1列目）
        - 将来: Meta広告（2列目）、Yahoo!広告（3列目）を追加予定
        
        各列の構成:
        - 1行目: プラットフォーム見出しカード
        - 以降: メトリクスカード（IMP, Cost, CTs, CTR, CPC, CV, CVR, CPA）
    --}}

    @if ($metrics && !$loading)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">

            {{-- 1列目1行目: Google広告 --}}
            {{-- TODO: 将来的にMeta広告、Yahoo!広告も追加可能な構造にする --}}
            <div class="rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow relative overflow-hidden"
                style="background-color: #4285F4;">
                {{-- Googleロゴ4色のアクセントストライプ --}}
                <div class="absolute top-0 left-0 right-0 h-1 flex">
                    <div class="flex-1" style="background-color: #EA4335;"></div>
                    <div class="flex-1" style="background-color: #FBBC05;"></div>
                    <div class="flex-1" style="background-color: #34A853;"></div>
                    <div class="flex-1" style="background-color: #4285F4;"></div>
                </div>
                <div class="flex items-center gap-3 mt-1">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center relative overflow-hidden"
                        style="background-color: rgba(255, 255, 255, 0.2);">
                        {{-- Googleロゴ4色のアイコン背景 --}}
                        <div class="absolute inset-0 opacity-30">
                            <div class="absolute top-0 left-0 w-1/2 h-1/2" style="background-color: #EA4335;"></div>
                            <div class="absolute top-0 right-0 w-1/2 h-1/2" style="background-color: #FBBC05;"></div>
                            <div class="absolute bottom-0 left-0 w-1/2 h-1/2" style="background-color: #34A853;"></div>
                            <div class="absolute bottom-0 right-0 w-1/2 h-1/2" style="background-color: #4285F4;"></div>
                        </div>
                        <svg class="w-7 h-7 text-white relative z-10" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white" style="color: white;">Google広告</h3>
                        <p class="text-sm text-white/90" style="color: rgba(255, 255, 255, 0.9);">広告パフォーマンス</p>
                    </div>
                </div>
            </div>

            {{-- 2列目1行目: クリック数 (CTs) --}}
            <div class="bg-zinc-900 rounded-xl shadow-sm border border-zinc-700 p-6 hover:shadow-md transition-shadow cursor-pointer"
                wire:click="showMetricGraph('clicks')">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center"
                                style="background-color: rgba(168, 85, 247, 0.3);">
                                <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-400">クリック数</p>
                                <p class="text-xs text-gray-500">CTs</p>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-white mb-2">
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

            {{-- 3列目1行目: コンバージョン (CV) --}}
            <div class="bg-zinc-900 rounded-xl shadow-sm border border-zinc-700 p-6 hover:shadow-md transition-shadow cursor-pointer"
                wire:click="showMetricGraph('conversions')">
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
                            <div>
                                <p class="text-sm font-medium text-gray-400">コンバージョン</p>
                                <p class="text-xs text-gray-500">CV</p>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-white mb-2">
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

            {{-- 1列目2行目: 費用 (Cost) --}}
            <div class="bg-zinc-900 rounded-xl shadow-sm border border-zinc-700 p-6 hover:shadow-md transition-shadow cursor-pointer"
                wire:click="showMetricGraph('cost')">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center"
                                style="background-color: rgba(249, 115, 22, 0.3);">
                                <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.05.402 2.75 1.015M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.05-.402-2.75-1.015M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-400">費用</p>
                                <p class="text-xs text-gray-500">Cost</p>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-white mb-2">
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

            {{-- 2列目2行目: クリック率 (CTR) --}}
            <div class="bg-zinc-900 rounded-xl shadow-sm border border-zinc-700 p-6 hover:shadow-md transition-shadow cursor-pointer"
                wire:click="showMetricGraph('ctr')">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center"
                                style="background-color: rgba(99, 102, 241, 0.3);">
                                <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-400">クリック率</p>
                                <p class="text-xs text-gray-500">CTR</p>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-white mb-2">
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

            {{-- 3列目2行目: コンバージョン率 (CVR) --}}
            <div class="bg-zinc-900 rounded-xl shadow-sm border border-zinc-700 p-6 hover:shadow-md transition-shadow cursor-pointer"
                wire:click="showMetricGraph('cvr')">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center"
                                style="background-color: rgba(6, 182, 212, 0.3);">
                                <svg class="w-6 h-6 text-cyan-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-400">コンバージョン率</p>
                                <p class="text-xs text-gray-500">CVR</p>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-white mb-2">
                            {{ number_format($metrics['cvr']['value'], 2) }}%
                        </p>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-green-50 rounded-md">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                            <span
                                class="text-sm font-semibold text-green-700">+{{ number_format($metrics['cvr']['change'], 1) }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 1列目3行目: インプレッション (IMP) --}}
            <div class="bg-zinc-900 rounded-xl shadow-sm border border-zinc-700 p-6 hover:shadow-md transition-shadow cursor-pointer"
                wire:click="showMetricGraph('impressions')">
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
                            <div>
                                <p class="text-sm font-medium text-gray-400">インプレッション</p>
                                <p class="text-xs text-gray-500">IMP</p>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-white mb-2">
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

            {{-- 2列目3行目: クリック単価 (CPC) --}}
            <div class="bg-zinc-900 rounded-xl shadow-sm border border-zinc-700 p-6 hover:shadow-md transition-shadow cursor-pointer"
                wire:click="showMetricGraph('cpc')">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center"
                                style="background-color: rgba(20, 184, 166, 0.3);">
                                <svg class="w-6 h-6 text-teal-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.05.402 2.75 1.015M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.05-.402-2.75-1.015M15 8a3 3 0 11-6 0 3 3 0 016 0zm3 11a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-400">クリック単価</p>
                                <p class="text-xs text-gray-500">CPC</p>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-white mb-2">
                            ¥{{ number_format($metrics['cpc']['value'], 2) }}
                        </p>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-green-50 rounded-md">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6" />
                            </svg>
                            <span
                                class="text-sm font-semibold text-green-700">{{ number_format($metrics['cpc']['change'], 1) }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3列目3行目: CPA --}}
            <div class="bg-zinc-900 rounded-xl shadow-sm border border-zinc-700 p-6 hover:shadow-md transition-shadow cursor-pointer"
                wire:click="showMetricGraph('cpa')">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center"
                                style="background-color: rgba(236, 72, 153, 0.3);">
                                <svg class="w-6 h-6 text-pink-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-400">獲得単価</p>
                                <p class="text-xs text-gray-500">CPA</p>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-white mb-2">
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
        <div class="bg-zinc-900 rounded-2xl shadow-sm border border-zinc-700 p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: #4285F4;">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-white">クイックアクション</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="/reports/generate" class="group p-6 border rounded-xl transition-colors"
                    style="background-color: #EA4335; border-color: #C62828;">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mb-4"
                        style="background-color: rgba(255, 255, 255, 0.2);">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-white mb-1">AIレポート生成</h3>
                    <p class="text-sm text-white/90">Geminiで効果分析</p>
                </a>

                <a href="/insights" class="group p-6 border rounded-xl transition-colors"
                    style="background-color: #FBBC05; border-color: #F9AB00;">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mb-4"
                        style="background-color: rgba(255, 255, 255, 0.2);">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-white mb-1">インサイト確認</h3>
                    <p class="text-sm text-white/90">データから洞察</p>
                </a>

                <a href="/recommendations" class="group p-6 border rounded-xl transition-colors"
                    style="background-color: #34A853; border-color: #2E7D32;">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mb-4"
                        style="background-color: rgba(255, 255, 255, 0.2);">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-white mb-1">改善施策実施</h3>
                    <p class="text-sm text-white/90">AI提案を確認</p>
                </a>
            </div>
        </div>
    @endif

    {{-- グラフモーダル --}}
    @if ($showGraphModal && $dailyData)
        <div class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 p-4"
            wire:click="closeGraphModal">
            <div class="bg-zinc-900 rounded-2xl shadow-2xl max-w-5xl w-full max-h-[90vh] overflow-auto border border-zinc-700"
                x-data="{
                    init() {
                            // Chart.jsのグラフを作成
                            const ctx = this.$refs.chartCanvas;
                            const data = @js($dailyData['values']);
                            const dates = @js($dailyData['dates']);
                            const metricName = @js($dailyData['name']);
                
                            this.chart = new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: dates,
                                    datasets: [{
                                        label: metricName,
                                        data: data,
                                        borderColor: '#4285F4',
                                        backgroundColor: 'rgba(66, 133, 244, 0.1)',
                                        tension: 0.4,
                                        fill: true,
                                        pointRadius: 3,
                                        pointHoverRadius: 6,
                                        pointBackgroundColor: '#4285F4',
                                        pointBorderColor: '#fff',
                                        pointBorderWidth: 2,
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            display: true,
                                            labels: {
                                                color: '#9CA3AF'
                                            }
                                        },
                                        tooltip: {
                                            mode: 'index',
                                            intersect: false,
                                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                            titleColor: '#fff',
                                            bodyColor: '#fff',
                                            borderColor: '#4285F4',
                                            borderWidth: 1
                                        }
                                    },
                                    scales: {
                                        x: {
                                            ticks: {
                                                color: '#9CA3AF',
                                                maxTicksLimit: 10
                                            },
                                            grid: {
                                                color: 'rgba(161, 161, 170, 0.1)'
                                            }
                                        },
                                        y: {
                                            ticks: {
                                                color: '#9CA3AF'
                                            },
                                            grid: {
                                                color: 'rgba(161, 161, 170, 0.1)'
                                            },
                                            beginAtZero: true
                                        }
                                    },
                                    interaction: {
                                        mode: 'index',
                                        intersect: false
                                    }
                                }
                            });
                        },
                        destroy() {
                            if (this.chart) {
                                this.chart.destroy();
                            }
                        }
                }" wire:click.stop>
                <div class="p-6 border-b border-zinc-700 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center"
                            style="background-color: #4285F4;">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-white">{{ $dailyData['name'] }} - 日別推移</h2>
                            @if ($dateRange)
                                <p class="text-sm text-gray-400">
                                    {{ $dateRange['start']->format('Y年m月d日') }} ～
                                    {{ $dateRange['end']->format('Y年m月d日') }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <button wire:click="closeGraphModal" type="button"
                        class="p-2 text-gray-400 hover:text-white hover:bg-zinc-800 rounded-lg transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-6">
                    <div style="height: 400px;">
                        <canvas x-ref="chartCanvas"></canvas>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
