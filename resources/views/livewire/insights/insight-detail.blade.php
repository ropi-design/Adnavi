<?php

use App\Models\Insight;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{state, mount};

state([
    'insight' => null,
    'loading' => false,
]);

mount(function ($id) {
    $this->loadInsight($id);
});

$loadInsight = function ($id) {
    $this->loading = true;

    $this->insight = Insight::with(['analysisReport.adAccount', 'recommendations'])
        ->whereHas('analysisReport', fn($q) => $q->where('user_id', Auth::id()))
        ->findOrFail($id);

    $this->loading = false;
};

$implementRecommendation = function ($recommendationId) {
    $recommendation = $this->insight->recommendations()->findOrFail($recommendationId);
    $recommendation->update(['status' => 'implemented']);

    $this->loadInsight($this->insight->id);

    session()->flash('message', '施策を実施済みとしてマークしました');
};

?>

<div class="p-6 lg:p-8 space-y-6 animate-fade-in">
    {{-- ローディング --}}
    <div wire:loading wire:target="loadInsight" class="flex flex-col items-center justify-center py-16">
        <svg class="w-12 h-12 text-blue-600 animate-spin mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        <p class="text-gray-600 font-medium">インサイトを読み込んでいます...</p>
    </div>

    @if ($insight && !$loading)
        {{-- ヘッダー --}}
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-4">
                    @php
                        $priorityConfig = match ($insight->priority->value) {
                            'high' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => '高'],
                            'medium' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'label' => '中'],
                            'low' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => '低'],
                        };
                        $categoryLabel = match ($insight->category->value) {
                            'performance' => 'パフォーマンス',
                            'budget' => '予算',
                            'targeting' => 'ターゲティング',
                            'creative' => 'クリエイティブ',
                            'conversion' => 'コンバージョン',
                        };
                    @endphp
                    <span
                        class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-bold {{ $priorityConfig['bg'] }} {{ $priorityConfig['text'] }}">
                        優先度: {{ $priorityConfig['label'] }}
                    </span>
                    <span
                        class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-purple-100 text-purple-800">
                        {{ $categoryLabel }}
                    </span>
                </div>

                <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $insight->title }}</h1>
                <p class="text-lg text-gray-700 leading-relaxed">{{ $insight->description }}</p>

                <div class="flex items-center gap-6 mt-6">
                    <div class="flex items-center gap-2">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span class="text-gray-600">インパクト:</span>
                        <span class="text-2xl font-bold text-gray-900">{{ $insight->impact_score }}/10</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-gray-600">信頼度:</span>
                        <span
                            class="text-2xl font-bold text-gray-900">{{ number_format($insight->confidence_score * 100) }}%</span>
                    </div>
                </div>
            </div>

            <a href="/reports/{{ $insight->analysisReport->id }}" class="btn btn-secondary">
                レポートを見る
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

        {{-- データ詳細 --}}
        @if ($insight->data_points)
            <div class="card p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">データポイント</h2>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <pre class="text-sm text-gray-700 whitespace-pre-wrap">{{ json_encode($insight->data_points, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
        @endif

        {{-- 改善施策 --}}
        @if ($insight->recommendations->count() > 0)
            <div class="card p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">関連する改善施策</h2>

                <div class="space-y-4">
                    @foreach ($insight->recommendations as $recommendation)
                        <div
                            class="p-6 border-2 border-gray-200 rounded-xl hover:border-blue-300 hover:shadow-lg transition-all">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-3">
                                        @php
                                            $statusConfig = match ($recommendation->status->value) {
                                                'pending' => [
                                                    'bg' => 'bg-gray-100',
                                                    'text' => 'text-gray-800',
                                                    'label' => '未着手',
                                                ],
                                                'in_progress' => [
                                                    'bg' => 'bg-blue-100',
                                                    'text' => 'text-blue-800',
                                                    'label' => '実施中',
                                                ],
                                                'implemented' => [
                                                    'bg' => 'bg-green-100',
                                                    'text' => 'text-green-800',
                                                    'label' => '実施済み',
                                                ],
                                                'dismissed' => [
                                                    'bg' => 'bg-red-100',
                                                    'text' => 'text-red-800',
                                                    'label' => '却下',
                                                ],
                                            };
                                        @endphp
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                                            {{ $statusConfig['label'] }}
                                        </span>
                                    </div>

                                    <h4 class="font-bold text-xl text-gray-900 mb-2">{{ $recommendation->title }}</h4>
                                    <p class="text-gray-600 mb-4">{{ $recommendation->description }}</p>

                                    <div class="flex items-center gap-6 text-sm">
                                        <div>
                                            <span class="text-gray-600">難易度:</span>
                                            <span class="font-semibold text-gray-900">
                                                {{ match ($recommendation->implementation_difficulty) {
                                                    'easy' => '簡単',
                                                    'medium' => '普通',
                                                    'hard' => '難しい',
                                                } }}
                                            </span>
                                        </div>
                                        @if ($recommendation->estimated_impact)
                                            <div>
                                                <span class="text-gray-600">推定効果:</span>
                                                <span
                                                    class="font-semibold text-gray-900">{{ $recommendation->estimated_impact }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex flex-col gap-2">
                                    <a href="/recommendations/{{ $recommendation->id }}"
                                        class="btn btn-primary text-sm">
                                        詳細
                                    </a>
                                    @if ($recommendation->status->value === 'pending')
                                        <button wire:click="implementRecommendation({{ $recommendation->id }})"
                                            class="btn btn-success text-sm">
                                            実施済みにする
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- メタ情報 --}}
        <div class="card p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">メタ情報</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-600">レポート:</span>
                    <span
                        class="font-semibold text-gray-900">{{ $insight->analysisReport->adAccount->account_name }}</span>
                </div>
                <div>
                    <span class="text-gray-600">作成日:</span>
                    <span
                        class="font-semibold text-gray-900">{{ $insight->created_at->isoFormat('YYYY年MM月DD日 HH:mm') }}</span>
                </div>
            </div>
        </div>
    @endif
</div>
