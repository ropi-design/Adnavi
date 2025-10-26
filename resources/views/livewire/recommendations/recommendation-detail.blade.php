<?php

use App\Models\Recommendation;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{state, mount};

// 状態
state([
    'recommendation' => null,
    'loading' => false,
]);

// マウント時の処理
mount(function ($id) {
    $this->loadRecommendation($id);
});

// 施策読み込み
$loadRecommendation = function ($id) {
    $this->loading = true;

    $this->recommendation = Recommendation::with(['insight.analysisReport.adAccount'])
        ->whereHas('insight.analysisReport', fn($q) => $q->where('user_id', Auth::id()))
        ->findOrFail($id);

    $this->loading = false;
};

$updateStatus = function ($status) {
    $this->recommendation->update(['status' => $status]);

    if ($status === 'implemented') {
        $this->recommendation->update(['implemented_at' => now()]);
    }

    $this->loadRecommendation($this->recommendation->id);

    session()->flash('message', 'ステータスを更新しました');
};

?>

<div class="space-y-6">
    <div wire:loading wire:target="loadRecommendation" class="text-center py-12">
        <flux:icon.arrow-path class="w-8 h-8 animate-spin mx-auto text-gray-400" />
    </div>

    @if ($recommendation && !$loading)
        {{-- ヘッダー --}}
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-4">
                    @php
                        $statusColor = match ($recommendation->status) {
                            'pending' => 'neutral',
                            'in_progress' => 'info',
                            'implemented' => 'success',
                            'dismissed' => 'ghost',
                        };
                    @endphp
                    <flux:badge :variant="$statusColor" size="lg">
                        {{ match ($recommendation->status) {
                            'pending' => '未着手',
                            'in_progress' => '実施中',
                            'implemented' => '実施済み',
                            'dismissed' => '却下',
                        } }}
                    </flux:badge>

                    <flux:badge variant="ghost">
                        {{ match ($recommendation->implementation_difficulty) {
                            'easy' => '難易度: 簡単',
                            'medium' => '難易度: 普通',
                            'hard' => '難易度: 難しい',
                        } }}
                    </flux:badge>

                    @if ($recommendation->estimated_impact)
                        <flux:badge variant="ghost">
                            期待効果: {{ $recommendation->estimated_impact }}
                        </flux:badge>
                    @endif
                </div>

                <h1 class="text-3xl font-bold">{{ $recommendation->title }}</h1>
                <p class="text-gray-600 mt-2">{{ $recommendation->description }}</p>
            </div>

            <flux:button href="/insights/{{ $recommendation->insight->id }}" wire:navigate variant="ghost">
                元のインサイトを見る
            </flux:button>
        </div>

        {{-- 具体的な実施手順 --}}
        @if ($recommendation->specific_actions && count($recommendation->specific_actions) > 0)
            <flux:card>
                <flux:heading>具体的な実施手順</flux:heading>

                <div class="mt-6 space-y-3">
                    @foreach ($recommendation->specific_actions as $index => $action)
                        <div class="flex gap-3 p-4 bg-gray-50 rounded-lg">
                            <div
                                class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">
                                {{ $index + 1 }}
                            </div>
                            <div class="flex-1">
                                <p class="text-gray-900">{{ $action }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </flux:card>
        @endif

        {{-- ステータス変更 --}}
        <flux:card>
            <flux:heading>ステータス管理</flux:heading>

            <div class="mt-6 space-y-3">
                @if ($recommendation->status === 'pending')
                    <flux:button wire:click="updateStatus('in_progress')" variant="primary" icon="play">
                        実施を開始する
                    </flux:button>
                @endif

                @if ($recommendation->status === 'in_progress')
                    <div class="flex gap-3">
                        <flux:button wire:click="updateStatus('implemented')" variant="success" icon="check">
                            実施完了とする
                        </flux:button>
                        <flux:button wire:click="updateStatus('dismissed')" variant="danger" icon="x-mark">
                            却下する
                        </flux:button>
                    </div>
                @endif

                @if ($recommendation->status === 'implemented' && $recommendation->implemented_at)
                    <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex items-center gap-2 text-green-800">
                            <flux:icon.check-circle class="w-5 h-5" />
                            <span class="font-semibold">実施済み</span>
                        </div>
                        <p class="text-sm text-green-600 mt-1">
                            実施日:
                            {{ \Carbon\Carbon::parse($recommendation->implemented_at)->isoFormat('YYYY年MM月DD日 HH:mm') }}
                        </p>
                    </div>
                @endif
            </div>
        </flux:card>

        {{-- 関連情報 --}}
        <flux:card>
            <flux:heading>関連情報</flux:heading>
            <div class="mt-4 space-y-2 text-sm">
                <div class="flex items-center gap-2">
                    <span class="font-semibold">元のインサイト:</span>
                    <a href="/insights/{{ $recommendation->insight->id }}" wire:navigate
                        class="text-blue-600 hover:underline">
                        {{ $recommendation->insight->title }}
                    </a>
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-semibold">分析レポート:</span>
                    <a href="/reports/{{ $recommendation->insight->analysisReport->id }}" wire:navigate
                        class="text-blue-600 hover:underline">
                        {{ $recommendation->insight->analysisReport->adAccount->account_name }}
                    </a>
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-semibold">アクションタイプ:</span>
                    <span class="text-gray-600">{{ $recommendation->action_type }}</span>
                </div>
            </div>
        </flux:card>

    @endif

    @if (session('message'))
        <flux:alert variant="success">
            {{ session('message') }}
        </flux:alert>
    @endif
</div>
