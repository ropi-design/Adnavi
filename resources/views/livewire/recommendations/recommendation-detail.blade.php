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
        {{-- 戻るボタン --}}
        <div class="mb-4">
            <a href="/recommendations"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-colors border-2"
                style="background-color: #ffffff; color: #000000; border-color: #e5e7eb;">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                戻る
            </a>
        </div>

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

                <h1 class="text-4xl font-bold mb-4" style="color: #ffffff;">{{ $recommendation->title }}</h1>
                <p class="text-lg leading-relaxed" style="color: #ffffff;">{{ $recommendation->description }}</p>
            </div>

            <a href="/insights/{{ $recommendation->insight->id }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-colors border-2"
                style="background-color: #ffffff; color: #000000; border-color: #e5e7eb;">
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
            <h2 class="text-2xl font-bold mb-4" style="color: #ffffff;">ステータス管理</h2>
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
                <h3 class="text-xl font-bold mb-4" style="color: #ffffff;">実施難易度</h3>
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
                        <p class="text-2xl font-bold" style="color: #ffffff;">{{ $difficultyConfig['label'] }}</p>
                        <p class="text-sm" style="color: #ffffff;">{{ $difficultyConfig['desc'] }}</p>
                    </div>
                </div>
            </div>

            @if ($recommendation->estimated_impact)
                <div class="p-6 rounded-xl border-2" style="background-color: #ffffff; border-color: #e5e7eb;">
                    <h3 class="text-xl font-bold mb-4"
                        style="color: #000000; text-shadow: 2px 2px 4px rgba(0,0,0,0.1);">推定効果</h3>
                    <p class="text-3xl font-bold" style="color: #667eea;">
                        {{ $recommendation->estimated_impact }}</p>
                </div>
            @endif
        </div>

        {{-- 実施手順 --}}
        @if ($recommendation->implementation_steps)
            <div class="card p-6">
                <h2 class="text-2xl font-bold mb-6" style="color: #ffffff;">実施手順</h2>
                <div class="space-y-4">
                    @foreach ($recommendation->implementation_steps as $index => $step)
                        <div class="flex gap-4">
                            <div
                                class="flex-shrink-0 w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">
                                {{ $index + 1 }}
                            </div>
                            <div class="flex-1 pt-1">
                                <p style="color: #ffffff;">{{ $step }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- 関連インサイト --}}
        <div class="card p-6">
            <h2 class="text-2xl font-bold mb-4" style="color: #ffffff;">関連インサイト</h2>
            <div class="p-6 border-2 rounded-xl"
                style="background: linear-gradient(to right, #faf5ff, #eff6ff); border-color: #d8b4fe;">
                <h4 class="font-bold text-xl mb-2" style="color: #000000;">{{ $recommendation->insight->title }}</h4>
                <p class="mb-4" style="color: #000000;">{{ $recommendation->insight->description }}</p>
                <div class="flex items-center gap-6 text-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span style="color: #000000;">インパクト:</span>
                        <span class="font-bold"
                            style="color: #000000;">{{ $recommendation->insight->impact_score }}/10</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span style="color: #000000;">信頼度:</span>
                        <span class="font-bold"
                            style="color: #000000;">{{ number_format($recommendation->insight->confidence_score * 100) }}%</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- メタ情報 --}}
        <div class="card p-6">
            <h2 class="text-2xl font-bold mb-4" style="color: #ffffff;">メタ情報</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span style="color: #ffffff;">アカウント:</span>
                    <span class="font-semibold"
                        style="color: #ffffff;">{{ $recommendation->insight->analysisReport->adAccount->account_name }}</span>
                </div>
                <div>
                    <span style="color: #ffffff;">作成日:</span>
                    <span class="font-semibold"
                        style="color: #ffffff;">{{ $recommendation->created_at->isoFormat('YYYY年MM月DD日 HH:mm') }}</span>
                </div>
            </div>
        </div>
    @endif
</div>
