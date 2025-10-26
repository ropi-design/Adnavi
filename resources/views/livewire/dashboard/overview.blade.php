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

?>

<div class="space-y-6">
    {{-- 期間選択 --}}
    <div class="flex items-center justify-between">
        <div class="flex gap-2">
            <flux:button variant="{{ $selectedPeriod === 'today' ? 'primary' : 'ghost' }}" size="sm"
                wire:click="changePeriod('today')">
                今日
            </flux:button>
            <flux:button variant="{{ $selectedPeriod === 'yesterday' ? 'primary' : 'ghost' }}" size="sm"
                wire:click="changePeriod('yesterday')">
                昨日
            </flux:button>
            <flux:button variant="{{ $selectedPeriod === 'week' ? 'primary' : 'ghost' }}" size="sm"
                wire:click="changePeriod('week')">
                今週
            </flux:button>
            <flux:button variant="{{ $selectedPeriod === 'month' ? 'primary' : 'ghost' }}" size="sm"
                wire:click="changePeriod('month')">
                今月
            </flux:button>
        </div>

        <flux:button wire:click="refresh" icon="arrow-path" variant="ghost" size="sm">
            <span wire:loading.remove wire:target="refresh">更新</span>
            <span wire:loading wire:target="refresh">更新中...</span>
        </flux:button>
    </div>

    {{-- ローディング状態 --}}
    <div wire:loading wire:target="loadMetrics" class="text-center py-8">
        <flux:icon.arrow-path class="w-8 h-8 animate-spin mx-auto text-gray-400" />
        <p class="mt-2 text-sm text-gray-500">データを読み込んでいます...</p>
    </div>

    {{-- メトリクスカード --}}
    @if ($metrics && !$loading)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {{-- インプレッション --}}
            <flux:card>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500">インプレッション</span>
                        <flux:icon.eye class="w-5 h-5 text-gray-400" />
                    </div>

                    <div class="flex items-end justify-between">
                        <div class="text-3xl font-bold">
                            {{ number_format($metrics['impressions']['value']) }}
                        </div>

                        <div
                            class="flex items-center gap-1 text-sm {{ $this->getTrendColor($metrics['impressions']['trend']) }}">
                            <flux:icon :name="$this->getTrendIcon($metrics['impressions']['trend'])" class="w-4 h-4" />
                            <span>{{ number_format(abs($metrics['impressions']['change']), 1) }}%</span>
                        </div>
                    </div>
                </div>
            </flux:card>

            {{-- クリック数 --}}
            <flux:card>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500">クリック数</span>
                        <flux:icon.cursor-arrow-rays class="w-5 h-5 text-gray-400" />
                    </div>

                    <div class="flex items-end justify-between">
                        <div class="text-3xl font-bold">
                            {{ number_format($metrics['clicks']['value']) }}
                        </div>

                        <div
                            class="flex items-center gap-1 text-sm {{ $this->getTrendColor($metrics['clicks']['trend']) }}">
                            <flux:icon :name="$this->getTrendIcon($metrics['clicks']['trend'])" class="w-4 h-4" />
                            <span>{{ number_format(abs($metrics['clicks']['change']), 1) }}%</span>
                        </div>
                    </div>
                </div>
            </flux:card>

            {{-- 費用 --}}
            <flux:card>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500">費用</span>
                        <flux:icon.currency-yen class="w-5 h-5 text-gray-400" />
                    </div>

                    <div class="flex items-end justify-between">
                        <div class="text-3xl font-bold">
                            ¥{{ number_format($metrics['cost']['value']) }}
                        </div>

                        <div
                            class="flex items-center gap-1 text-sm {{ $this->getTrendColor($metrics['cost']['trend'], true) }}">
                            <flux:icon :name="$this->getTrendIcon($metrics['cost']['trend'])" class="w-4 h-4" />
                            <span>{{ number_format(abs($metrics['cost']['change']), 1) }}%</span>
                        </div>
                    </div>
                </div>
            </flux:card>

            {{-- コンバージョン --}}
            <flux:card>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500">コンバージョン</span>
                        <flux:icon.check-circle class="w-5 h-5 text-gray-400" />
                    </div>

                    <div class="flex items-end justify-between">
                        <div class="text-3xl font-bold">
                            {{ number_format($metrics['conversions']['value']) }}
                        </div>

                        <div
                            class="flex items-center gap-1 text-sm {{ $this->getTrendColor($metrics['conversions']['trend']) }}">
                            <flux:icon :name="$this->getTrendIcon($metrics['conversions']['trend'])" class="w-4 h-4" />
                            <span>{{ number_format(abs($metrics['conversions']['change']), 1) }}%</span>
                        </div>
                    </div>
                </div>
            </flux:card>

            {{-- CTR --}}
            <flux:card>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500">CTR</span>
                        <flux:icon.cursor-arrow-ripple class="w-5 h-5 text-gray-400" />
                    </div>

                    <div class="flex items-end justify-between">
                        <div class="text-3xl font-bold">
                            {{ number_format($metrics['ctr']['value'], 2) }}%
                        </div>

                        <div
                            class="flex items-center gap-1 text-sm {{ $this->getTrendColor($metrics['ctr']['trend']) }}">
                            <flux:icon :name="$this->getTrendIcon($metrics['ctr']['trend'])" class="w-4 h-4" />
                            <span>{{ number_format(abs($metrics['ctr']['change']), 1) }}%</span>
                        </div>
                    </div>
                </div>
            </flux:card>

            {{-- CPA --}}
            <flux:card>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500">CPA</span>
                        <flux:icon.calculator class="w-5 h-5 text-gray-400" />
                    </div>

                    <div class="flex items-end justify-between">
                        <div class="text-3xl font-bold">
                            ¥{{ number_format($metrics['cpa']['value']) }}
                        </div>

                        <div
                            class="flex items-center gap-1 text-sm {{ $this->getTrendColor($metrics['cpa']['trend'], true) }}">
                            <flux:icon :name="$this->getTrendIcon($metrics['cpa']['trend'])" class="w-4 h-4" />
                            <span>{{ number_format(abs($metrics['cpa']['change']), 1) }}%</span>
                        </div>
                    </div>
                </div>
            </flux:card>
        </div>
    @endif

    {{-- クイックアクション --}}
    <flux:card>
        <flux:heading size="lg">クイックアクション</flux:heading>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3">
            <flux:button href="/reports/generate" wire:navigate icon="sparkles">
                AIレポート生成
            </flux:button>

            <flux:button href="/insights" wire:navigate icon="light-bulb" variant="ghost">
                インサイトを見る
            </flux:button>

            <flux:button href="/recommendations" wire:navigate icon="rocket-launch" variant="ghost">
                改善施策を確認
            </flux:button>
        </div>
    </flux:card>
</div>
