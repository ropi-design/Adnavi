<?php

use App\Models\Recommendation;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{state, mount};

state([
    'recommendation' => null,
    'loading' => false,
]);

mount(function ($id) {
    $this->loadRecommendation($id);
});

$loadRecommendation = function ($id) {
    $this->loading = true;

    $this->recommendation = Recommendation::with(['insight.analysisReport.adAccount'])
        ->whereHas('insight.analysisReport', fn($q) => $q->where('user_id', Auth::id()))
        ->findOrFail($id);

    $this->loading = false;
};

$updateStatus = function ($status) {
    $this->recommendation->update(['status' => $status]);
    $this->loadRecommendation($this->recommendation->id);

    session()->flash('message', 'ステータスを更新しました');
};

?>

<div class="p-6 lg:p-8 space-y-6 animate-fade-in">
    {{-- ローディング --}}
    <div wire:loading wire:target="loadRecommendation" class="flex flex-col items-center justify-center py-16">
        <svg class="w-12 h-12 text-blue-600 animate-spin mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        <p class="text-gray-600 font-medium">施策を読み込んでいます...</p>
    </div>

    @if ($recommendation && !$loading)
        {{-- ヘッダー --}}
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-4">
                    @php
                        $statusConfig = match ($recommendation->status->value) {
                            'pending' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => '未着手'],
                            'in_progress' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'label' => '実施中'],
                            'implemented' => [
                                'bg' => 'bg-green-100',
                                'text' => 'text-green-800',
                                'label' => '実施済み',
                            ],
                            'dismissed' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => '却下'],
                        };
                    @endphp
                    <span
                        class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-bold {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                        {{ $statusConfig['label'] }}
                    </span>
                </div>

                <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $recommendation->title }}</h1>
                <p class="text-lg text-gray-700 leading-relaxed">{{ $recommendation->description }}</p>
            </div>

            <a href="/insights/{{ $recommendation->insight->id }}" class="btn btn-secondary">
                インサイトを見る
            </a>
        </div>

        {{-- メッセージ --}}
        @if (session('message'))
            <div class="p-4 bg-green-100 border-l-4 border-green-500 rounded-lg">
                <div class="flex items-center gap-2 text-green-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ session('message') }}
                </div>
            </div>
        @endif

        {{-- ステータス変更 --}}
        <div class="card p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">ステータス管理</h2>
            <div class="flex flex-wrap gap-3">
                <button wire:click="updateStatus('pending')"
                    class="px-4 py-2 rounded-lg font-semibold transition-all {{ $recommendation->status->value === 'pending' ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    未着手
                </button>
                <button wire:click="updateStatus('in_progress')"
                    class="px-4 py-2 rounded-lg font-semibold transition-all {{ $recommendation->status->value === 'in_progress' ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-700 hover:bg-blue-200' }}">
                    実施中
                </button>
                <button wire:click="updateStatus('implemented')"
                    class="px-4 py-2 rounded-lg font-semibold transition-all {{ $recommendation->status->value === 'implemented' ? 'bg-green-600 text-white' : 'bg-green-100 text-green-700 hover:bg-green-200' }}">
                    実施済み
                </button>
                <button wire:click="updateStatus('dismissed')"
                    class="px-4 py-2 rounded-lg font-semibold transition-all {{ $recommendation->status->value === 'dismissed' ? 'bg-red-600 text-white' : 'bg-red-100 text-red-700 hover:bg-red-200' }}">
                    却下
                </button>
            </div>
        </div>

        {{-- 実施詳細 --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="card p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">実施難易度</h3>
                <div class="flex items-center gap-3">
                    @php
                        $difficultyConfig = match ($recommendation->implementation_difficulty) {
                            'easy' => ['icon' => '🟢', 'label' => '簡単', 'desc' => 'すぐに実施可能'],
                            'medium' => ['icon' => '🟡', 'label' => '普通', 'desc' => '準備が必要'],
                            'hard' => ['icon' => '🔴', 'label' => '難しい', 'desc' => '慎重な計画が必要'],
                        };
                    @endphp
                    <span class="text-4xl">{{ $difficultyConfig['icon'] }}</span>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $difficultyConfig['label'] }}</p>
                        <p class="text-sm text-gray-600">{{ $difficultyConfig['desc'] }}</p>
                    </div>
                </div>
            </div>

            @if ($recommendation->estimated_impact)
                <div class="card p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">推定効果</h3>
                    <p class="text-3xl font-bold text-blue-600">{{ $recommendation->estimated_impact }}</p>
                </div>
            @endif
        </div>

        {{-- 実施手順 --}}
        @if ($recommendation->implementation_steps)
            <div class="card p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">実施手順</h2>
                <div class="space-y-4">
                    @foreach ($recommendation->implementation_steps as $index => $step)
                        <div class="flex gap-4">
                            <div
                                class="flex-shrink-0 w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">
                                {{ $index + 1 }}
                            </div>
                            <div class="flex-1 pt-1">
                                <p class="text-gray-700">{{ $step }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- 関連インサイト --}}
        <div class="card p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">関連インサイト</h2>
            <div class="p-6 bg-gradient-to-r from-purple-50 to-blue-50 border-2 border-purple-200 rounded-xl">
                <h4 class="font-bold text-xl text-gray-900 mb-2">{{ $recommendation->insight->title }}</h4>
                <p class="text-gray-700 mb-4">{{ $recommendation->insight->description }}</p>
                <div class="flex items-center gap-6 text-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span class="text-gray-600">インパクト:</span>
                        <span class="font-bold">{{ $recommendation->insight->impact_score }}/10</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-gray-600">信頼度:</span>
                        <span
                            class="font-bold">{{ number_format($recommendation->insight->confidence_score * 100) }}%</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- メタ情報 --}}
        <div class="card p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">メタ情報</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-600">アカウント:</span>
                    <span
                        class="font-semibold text-gray-900">{{ $recommendation->insight->analysisReport->adAccount->account_name }}</span>
                </div>
                <div>
                    <span class="text-gray-600">作成日:</span>
                    <span
                        class="font-semibold text-gray-900">{{ $recommendation->created_at->isoFormat('YYYY年MM月DD日 HH:mm') }}</span>
                </div>
            </div>
        </div>
    @endif
</div>
