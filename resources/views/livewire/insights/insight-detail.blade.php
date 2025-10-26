<?php

use App\Models\Insight;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{state, mount};

// 状態
state([
    'insight' => null,
    'loading' => false,
]);

// マウント時の処理
mount(function ($id) {
    $this->loadInsight($id);
});

// インサイト読み込み
$loadInsight = function ($id) {
    $this->loading = true;

    $this->insight = Insight::with(['analysisReport.adAccount', 'recommendations'])
        ->whereHas('analysisReport', fn($q) => $q->where('user_id', Auth::id()))
        ->findOrFail($id);

    $this->loading = false;
};

$implementRecommendation = function ($recommendationId) {
    $recommendation = $this->insight->recommendations()->findOrFail($recommendationId);
    $recommendation->markAsImplemented();

    $this->loadInsight($this->insight->id);

    session()->flash('message', '施策を実施済みとしてマークしました');
};

?>

<div class="space-y-6">
    <div wire:loading wire:target="loadInsight" class="text-center py-12">
        <flux:icon.arrow-path class="w-8 h-8 animate-spin mx-auto text-gray-400" />
    </div>

    @if ($insight && !$loading)
        {{-- ヘッダー --}}
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-4">
                    @php
                        $badgeColor = match ($insight->priority) {
                            'high' => 'danger',
                            'medium' => 'warning',
                            'low' => 'neutral',
                        };
                    @endphp
                    <flux:badge :variant="$badgeColor" size="lg">
                        {{ match ($insight->priority) {
                            'high' => '高',
                            'medium' => '中',
                            'low' => '低',
                        } }}
                        優先度
                    </flux:badge>

                    <flux:badge variant="ghost">
                        {{ match ($insight->category) {
                            'performance' => 'パフォーマンス',
                            'budget' => '予算',
                            'targeting' => 'ターゲティング',
                            'creative' => 'クリエイティブ',
                            'conversion' => 'コンバージョン',
                        } }}
                    </flux:badge>
                </div>

                <h1 class="text-3xl font-bold">{{ $insight->title }}</h1>
                <p class="text-gray-600 mt-2">{{ $insight->description }}</p>
            </div>

            <flux:button href="/reports/{{ $insight->analysisReport->id }}" wire:navigate variant="ghost">
                元のレポートを見る
            </flux:button>
        </div>

        {{-- スコア情報 --}}
        <div class="grid grid-cols-2 gap-4">
            <flux:card>
                <div class="space-y-2">
                    <div class="text-sm text-gray-500">インパクトスコア</div>
                    <div class="flex items-center gap-4">
                        <div class="text-4xl font-bold">{{ $insight->impact_score }}</div>
                        <span class="text-gray-400">/ 10</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $insight->impact_score * 10 }}%">
                        </div>
                    </div>
                </div>
            </flux:card>

            <flux:card>
                <div class="space-y-2">
                    <div class="text-sm text-gray-500">信頼度スコア</div>
                    <div class="flex items-center gap-4">
                        <div class="text-4xl font-bold">{{ number_format($insight->confidence_score * 100) }}</div>
                        <span class="text-gray-400">%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-600 h-2 rounded-full"
                            style="width: {{ $insight->confidence_score * 100 }}%"></div>
                    </div>
                </div>
            </flux:card>
        </div>

        {{-- 関連施策 --}}
        @if ($insight->recommendations->count() > 0)
            <flux:card>
                <flux:heading>関連する改善施策</flux:heading>

                <div class="mt-6 space-y-4">
                    @foreach ($insight->recommendations as $recommendation)
                        <div class="p-4 border border-gray-200 rounded-lg hover:border-blue-300 transition-colors">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        @php
                                            $statusColor = match ($recommendation->status) {
                                                'pending' => 'neutral',
                                                'in_progress' => 'info',
                                                'implemented' => 'success',
                                                'dismissed' => 'ghost',
                                            };
                                        @endphp
                                        <flux:badge :variant="$statusColor" size="sm">
                                            {{ match ($recommendation->status) {
                                                'pending' => '未着手',
                                                'in_progress' => '実施中',
                                                'implemented' => '実施済み',
                                                'dismissed' => '却下',
                                            } }}
                                        </flux:badge>

                                        <flux:badge variant="ghost" size="sm">
                                            {{ match ($recommendation->implementation_difficulty) {
                                                'easy' => '難易度: 簡単',
                                                'medium' => '難易度: 普通',
                                                'hard' => '難易度: 難しい',
                                            } }}
                                        </flux:badge>
                                    </div>

                                    <h4 class="font-semibold text-lg">{{ $recommendation->title }}</h4>
                                    <p class="text-sm text-gray-600 mt-1">{{ $recommendation->description }}</p>

                                    @if ($recommendation->estimated_impact)
                                        <div class="flex items-center gap-2 mt-3 text-sm">
                                            <flux:icon.trending-up class="w-4 h-4 text-green-600" />
                                            <span class="text-gray-600">期待効果:</span>
                                            <span
                                                class="font-semibold text-green-600">{{ $recommendation->estimated_impact }}</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="flex flex-col gap-2">
                                    @if ($recommendation->status === 'pending')
                                        <flux:button size="sm"
                                            wire:click="implementRecommendation({{ $recommendation->id }})">
                                            実施開始
                                        </flux:button>
                                    @endif
                                    <flux:button href="/recommendations/{{ $recommendation->id }}" wire:navigate
                                        variant="ghost" size="sm">
                                        詳細
                                    </flux:button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </flux:card>
        @else
            <flux:card>
                <div class="text-center py-8 text-gray-500">
                    <flux:icon.sparkles class="w-10 h-10 mx-auto mb-3 opacity-50" />
                    <p>このインサイトに対する改善施策はありません</p>
                </div>
            </flux:card>
        @endif

    @endif

    @if (session('message'))
        <flux:alert variant="success">
            {{ session('message') }}
        </flux:alert>
    @endif
</div>
