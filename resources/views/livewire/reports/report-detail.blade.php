<?php

use App\Models\AnalysisReport;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{state, mount};

// 状態
state([
    'report' => null,
    'loading' => false,
]);

// マウント時の処理
mount(function ($id) {
    $this->loadReport($id);
});

// レポート読み込み
$loadReport = function ($id) {
    $this->loading = true;

    $this->report = AnalysisReport::with(['adAccount', 'analyticsProperty', 'insights.recommendations'])
        ->where('user_id', Auth::id())
        ->findOrFail($id);

    $this->loading = false;
};

$deleteReport = function () {
    $this->report->delete();
    session()->flash('message', 'レポートを削除しました');

    $this->redirect('/reports', navigate: true);
};

?>

<div class="space-y-6">
    <div wire:loading wire:target="loadReport" class="text-center py-12">
        <flux:icon.arrow-path class="w-8 h-8 animate-spin mx-auto text-gray-400" />
    </div>

    @if ($report && !$loading)
        {{-- ヘッダー --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold">{{ $report->adAccount->account_name }}</h1>
                <p class="mt-1 text-sm text-gray-500">
                    {{ match ($report->report_type) {
                        'daily' => '日次レポート',
                        'weekly' => '週次レポート',
                        'monthly' => '月次レポート',
                        'custom' => 'カスタムレポート',
                    } }}
                    | {{ \Carbon\Carbon::parse($report->start_date)->isoFormat('YYYY年MM月DD日') }}
                    〜
                    {{ \Carbon\Carbon::parse($report->end_date)->isoFormat('YYYY年MM月DD日') }}
                </p>
            </div>

            <div class="flex gap-2">
                @php
                    $variant = match ($report->status) {
                        'completed' => 'success',
                        'processing' => 'info',
                        'failed' => 'danger',
                        default => 'neutral',
                    };
                @endphp
                <flux:badge :variant="$variant">
                    {{ match ($report->status) {
                        'pending' => '待機中',
                        'processing' => '処理中',
                        'completed' => '完了',
                        'failed' => '失敗',
                    } }}
                </flux:badge>

                <flux:button variant="danger" wire:click="deleteReport" wire:confirm="本当に削除しますか？">
                    削除
                </flux:button>
            </div>
        </div>

        @if ($report->status === 'completed')
            {{-- 概要 --}}
            <flux:card>
                <flux:heading>概要</flux:heading>
                <p class="mt-4 text-gray-600">
                    作成日: {{ $report->created_at->isoFormat('YYYY年MM月DD日 HH:mm') }}<br>
                    期間: {{ $report->start_date->isoFormat('YYYY/MM/DD') }} 〜
                    {{ $report->end_date->isoFormat('YYYY/MM/DD') }}<br>
                    @if ($report->analyticsProperty)
                        Analytics: {{ $report->analyticsProperty->property_name }}を含む
                    @endif
                </p>
            </flux:card>

            {{-- インサイト一覧 --}}
            @if ($report->insights->count() > 0)
                <flux:card>
                    <flux:heading>抽出されたインサイト</flux:heading>

                    <div class="mt-6 space-y-4">
                        @foreach ($report->insights as $insight)
                            <div class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <flux:badge
                                                :variant="match($insight->priority) {
                                                                                                                                                'high' => 'danger',
                                                                                                                                                'medium' => 'warning',
                                                                                                                                                'low' => 'neutral',
                                                                                                                                            }">
                                                {{ match ($insight->priority) {'high' => '高','medium' => '中','low' => '低'} }}
                                            </flux:badge>
                                            <span class="text-sm text-gray-500">
                                                {{ match ($insight->category) {
                                                    'performance' => 'パフォーマンス',
                                                    'budget' => '予算',
                                                    'targeting' => 'ターゲティング',
                                                    'creative' => 'クリエイティブ',
                                                    'conversion' => 'コンバージョン',
                                                } }}
                                            </span>
                                        </div>

                                        <h4 class="font-semibold text-lg">{{ $insight->title }}</h4>
                                        <p class="text-sm text-gray-600 mt-2">{{ $insight->description }}</p>

                                        <div class="flex items-center gap-4 mt-3 text-sm">
                                            <span>インパクト: {{ $insight->impact_score }}/10</span>
                                            <span>信頼度: {{ number_format($insight->confidence_score * 100) }}%</span>
                                        </div>
                                    </div>

                                    <flux:button href="/insights/{{ $insight->id }}" wire:navigate size="sm">
                                        詳細
                                    </flux:button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </flux:card>
            @endif
        @elseif($report->status === 'failed')
            <flux:card>
                <flux:alert variant="danger">
                    <p class="font-semibold">レポート生成に失敗しました</p>
                    <p class="mt-2 text-sm">{{ $report->error_message }}</p>
                </flux:alert>
            </flux:card>
        @else
            <flux:card>
                <div class="text-center py-12">
                    <flux:icon.clock class="w-12 h-12 mx-auto text-gray-400 mb-4" />
                    <p class="text-gray-600">レポートを生成中です...</p>
                    <p class="text-sm text-gray-500 mt-2">完了次第、通知いたします</p>
                </div>
            </flux:card>
        @endif

    @endif
</div>
