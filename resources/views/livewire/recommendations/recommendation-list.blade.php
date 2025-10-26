<?php

use App\Models\Recommendation;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use function Livewire\Volt\{state, with, uses};

uses([WithPagination::class]);

state([
    'search' => '',
    'statusFilter' => 'all',
    'difficultyFilter' => 'all',
]);

$updatingSearch = function () {
    $this->resetPage();
};

$updatingStatusFilter = function () {
    $this->resetPage();
};

$updatingDifficultyFilter = function () {
    $this->resetPage();
};

with(
    fn() => [
        'recommendations' => Recommendation::query()
            ->whereHas('insight.analysisReport', fn($q) => $q->where('user_id', Auth::id()))
            ->with(['insight.analysisReport.adAccount'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', "%{$this->search}%")->orWhere('description', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter !== 'all', fn($query) => $query->where('status', $this->statusFilter))
            ->when($this->difficultyFilter !== 'all', fn($query) => $query->where('implementation_difficulty', $this->difficultyFilter))
            ->orderByRaw(
                'CASE status 
            WHEN "pending" THEN 1 
            WHEN "in_progress" THEN 2 
            WHEN "implemented" THEN 3 
            WHEN "dismissed" THEN 4 
        END',
            )
            ->orderBy('created_at', 'desc')
            ->paginate(12),
    ],
);

?>

<div class="p-6 lg:p-8 space-y-6 animate-fade-in">
    {{-- ヘッダー --}}
    <div>
        <h1 class="text-4xl font-bold text-gray-900">改善施策</h1>
        <p class="text-gray-600 mt-1">AIが提案する具体的な改善アクション</p>
    </div>

    {{-- フィルター --}}
    <div class="card p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="col-span-2">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="施策を検索..."
                        class="form-input pl-10" />
                </div>
            </div>

            <select wire:model.live="statusFilter" class="form-input">
                <option value="all">全てのステータス</option>
                <option value="pending">未着手</option>
                <option value="in_progress">実施中</option>
                <option value="implemented">実施済み</option>
                <option value="dismissed">却下</option>
            </select>

            <select wire:model.live="difficultyFilter" class="form-input">
                <option value="all">全ての難易度</option>
                <option value="easy">簡単</option>
                <option value="medium">普通</option>
                <option value="hard">難しい</option>
            </select>
        </div>
    </div>

    {{-- 施策一覧 --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($recommendations as $recommendation)
            <div class="card p-6 hover:shadow-xl transition-all">
                <div class="space-y-4">
                    {{-- ヘッダー --}}
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="font-bold text-lg flex-1 text-gray-900">
                            {{ $recommendation->title }}
                        </h3>

                        @php
                            $statusConfig = match ($recommendation->status->value) {
                                'pending' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => '未着手'],
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
                                'dismissed' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => '却下'],
                            };
                        @endphp
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                            {{ $statusConfig['label'] }}
                        </span>
                    </div>

                    {{-- 説明 --}}
                    <p class="text-sm text-gray-600 line-clamp-3">
                        {{ $recommendation->description }}
                    </p>

                    {{-- メタ情報 --}}
                    <div class="flex flex-wrap items-center gap-4 text-sm">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span class="text-gray-500">難易度:</span>
                            <span class="font-semibold text-gray-900">
                                {{ match ($recommendation->implementation_difficulty) {
                                    'easy' => '簡単',
                                    'medium' => '普通',
                                    'hard' => '難しい',
                                } }}
                            </span>
                        </div>

                        @if ($recommendation->estimated_impact)
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                                <span class="text-gray-500">効果:</span>
                                <span class="font-semibold text-gray-900">{{ $recommendation->estimated_impact }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- アクション --}}
                    <div class="flex gap-2 pt-4 border-t border-gray-200">
                        <a href="/recommendations/{{ $recommendation->id }}"
                            class="btn btn-primary text-sm flex-1 justify-center">
                            詳細を見る
                        </a>

                        @if ($recommendation->status->value === 'pending')
                            <button class="btn btn-secondary text-sm">
                                実施開始
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="card">
                    <div class="text-center py-16 text-gray-500">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-6">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">改善施策がありません</h3>
                        <p class="text-gray-500 mb-6">レポートを生成して、AIによる提案を確認しましょう</p>
                        <a href="/reports/generate" class="btn btn-primary inline-flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                            レポートを生成
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- ページネーション --}}
    @if ($recommendations->hasPages())
        <div class="card p-4">
            {{ $recommendations->links() }}
        </div>
    @endif
</div>
