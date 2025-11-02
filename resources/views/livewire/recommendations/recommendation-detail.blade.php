<?php

use App\Models\Recommendation;
use App\Services\AI\GeminiService;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{state, mount};

state([
    'recommendation' => null,
    'loading' => false,
    'question' => '',
    'answer' => null,
    'asking' => false,
    'error' => null,
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

$askQuestion = function (GeminiService $geminiService) {
    if (empty($this->question)) {
        $this->error = '質問を入力してください';
        return;
    }

    $this->asking = true;
    $this->error = null;
    $this->answer = null;

    try {
        // 改善施策のデータを準備
        $recommendationData = [
            'title' => $this->recommendation->title,
            'description' => $this->recommendation->description,
            'estimated_impact' => $this->recommendation->estimated_impact,
            'implementation_difficulty' => $this->recommendation->implementation_difficulty,
            'specific_actions' => $this->recommendation->specific_actions ?? [],
        ];

        // Gemini APIに質問を送信
        $answer = $geminiService->askAboutRecommendation($this->question, $recommendationData);

        if ($answer) {
            $this->answer = $answer;
        } else {
            $this->error = '回答を取得できませんでした。もう一度お試しください。';
        }
    } catch (\Exception $e) {
        $this->error = 'エラーが発生しました: ' . $e->getMessage();
    } finally {
        $this->asking = false;
    }
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
                    <div class="space-y-3">
                        @php
                            // estimated_impactを | で分割して各行を表示
                            $impactLines = explode(' | ', $recommendation->estimated_impact);
                        @endphp
                        @foreach ($impactLines as $line)
                            <div class="flex items-start gap-2 p-3 bg-blue-50 rounded-lg">
                                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                                <p class="text-sm font-semibold" style="color: #1e40af;">
                                    {{ $line }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- 実施手順 --}}
        @if ($recommendation->specific_actions && count($recommendation->specific_actions) > 0)
            <div class="card p-6">
                <h2 class="text-2xl font-bold mb-6" style="color: #ffffff;">実施手順</h2>
                <div class="space-y-4">
                    @foreach ($recommendation->specific_actions as $index => $step)
                        <div class="flex gap-4 p-4 rounded-lg"
                            style="background-color: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2);">
                            <div
                                class="flex-shrink-0 w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">
                                {{ $index + 1 }}
                            </div>
                            <div class="flex-1 pt-1">
                                <p style="color: #ffffff; line-height: 1.7;">{{ $step }}</p>
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
                        @php
                            $impactLabel = match (true) {
                                $recommendation->insight->impact_score >= 8 => [
                                    'label' => '大',
                                    'bg' => 'bg-red-100',
                                    'text' => 'text-red-800',
                                ],
                                $recommendation->insight->impact_score >= 4 => [
                                    'label' => '中',
                                    'bg' => 'bg-yellow-100',
                                    'text' => 'text-yellow-800',
                                ],
                                default => ['label' => '小', 'bg' => 'bg-gray-100', 'text' => 'text-gray-800'],
                            };
                        @endphp
                        <span
                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $impactLabel['bg'] }} {{ $impactLabel['text'] }}">
                            {{ $impactLabel['label'] }}
                        </span>
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

        {{-- Gemini AI 質問機能 --}}
        <div class="card p-6">
            <h2 class="text-2xl font-bold mb-4" style="color: #ffffff;">
                <svg class="w-6 h-6 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
                AIに質問する
            </h2>
            <p class="text-sm mb-4" style="color: #ffffff; opacity: 0.8;">
                この改善施策について、Gemini AIに直接質問できます。実施方法や効果について詳しく知りたい場合は質問してください。
            </p>

            <form wire:submit="askQuestion" class="space-y-4">
                <div>
                    <textarea wire:model="question" rows="3" placeholder="例: この施策を実施する上で注意すべき点は？&#10;例: 期待できる効果についてもっと詳しく教えてください"
                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 focus:border-blue-500 focus:outline-none resize-none"
                        style="background-color: #ffffff; color: #000000;" wire:loading.attr="disabled"></textarea>
                    @error('question')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-3">
                    <button type="submit" wire:loading.attr="disabled"
                        class="px-6 py-3 rounded-lg font-semibold transition-all flex items-center gap-2"
                        style="background-color: #667eea; color: #ffffff;" wire:loading.class="opacity-50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            wire:loading.class="animate-spin" wire:target="askQuestion">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span wire:loading.remove wire:target="askQuestion">質問を送信</span>
                        <span wire:loading wire:target="askQuestion">回答を生成中...</span>
                    </button>
                    @if ($answer)
                        <button type="button" wire:click="$set('question', ''); $set('answer', null);"
                            class="px-6 py-3 rounded-lg font-semibold transition-all"
                            style="background-color: #e5e7eb; color: #000000;">
                            クリア
                        </button>
                    @endif
                </div>

                @if ($error)
                    <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                        <div class="flex items-center gap-2 text-red-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $error }}
                        </div>
                    </div>
                @endif

                @if ($answer)
                    <div class="p-6 bg-blue-50 border-2 border-blue-200 rounded-lg">
                        <div class="flex items-start gap-3 mb-3">
                            <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-1" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                            <h3 class="text-lg font-bold" style="color: #1e40af;">AI回答</h3>
                        </div>
                        <div class="prose max-w-none" style="color: #1e3a8a;">
                            <p class="whitespace-pre-wrap leading-relaxed">{{ $answer }}</p>
                        </div>
                    </div>
                @endif
            </form>
        </div>
    @endif
</div>
