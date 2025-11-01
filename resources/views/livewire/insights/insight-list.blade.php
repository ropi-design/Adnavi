<?php

use App\Models\Insight;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use function Livewire\Volt\{state, with, uses};

uses([WithPagination::class]);

state([
    'search' => '',
    'priorityFilter' => 'all',
    'categoryFilter' => 'all',
]);

$updatingSearch = function () {
    $this->resetPage();
};

$updatingPriorityFilter = function () {
    $this->resetPage();
};

$updatingCategoryFilter = function () {
    $this->resetPage();
};

with(
    fn() => [
        'insights' => Insight::query()
            ->whereHas('analysisReport', fn($q) => $q->where('user_id', Auth::id()))
            ->with(['analysisReport.adAccount'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', "%{$this->search}%")->orWhere('description', 'like', "%{$this->search}%");
                });
            })
            ->when($this->priorityFilter !== 'all', fn($query) => $query->where('priority', $this->priorityFilter))
            ->when($this->categoryFilter !== 'all', fn($query) => $query->where('category', $this->categoryFilter))
            ->orderByRaw(
                'CASE priority 
            WHEN "high" THEN 1 
            WHEN "medium" THEN 2 
            WHEN "low" THEN 3 
        END',
            )
            ->paginate(12),
    ],
);

?>

<div class="p-6 lg:p-8 space-y-6 animate-fade-in">
    {{-- ヘッダー --}}
    <div>
        <h1 class="text-4xl font-bold" style="color: #ffffff;">インサイト</h1>
        <p class="mt-1" style="color: #ffffff;">AIが発見したデータの洞察</p>
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
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="インサイトを検索..."
                        class="pl-10 w-full px-4 py-2.5 rounded-lg transition-colors"
                        style="background-color: #ffffff; color: #000000; border: 2px solid #e5e7eb;" />
                </div>
            </div>

            <select wire:model.live="priorityFilter" class="w-full px-4 py-2.5 rounded-lg transition-colors"
                style="background-color: #ffffff; color: #000000; border: 2px solid #e5e7eb;">
                <option value="all">全ての優先度</option>
                <option value="high">高</option>
                <option value="medium">中</option>
                <option value="low">低</option>
            </select>

            <select wire:model.live="categoryFilter" class="w-full px-4 py-2.5 rounded-lg transition-colors"
                style="background-color: #ffffff; color: #000000; border: 2px solid #e5e7eb;">
                <option value="all">全てのカテゴリ</option>
                <option value="performance">パフォーマンス</option>
                <option value="budget">予算</option>
                <option value="targeting">ターゲティング</option>
                <option value="creative">クリエイティブ</option>
                <option value="conversion">コンバージョン</option>
            </select>
        </div>
    </div>

    {{-- インサイト一覧 --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($insights as $insight)
            <a href="/insights/{{ $insight->id }}"
                class="p-6 hover:shadow-xl transition-all cursor-pointer group rounded-xl"
                style="background-color: #ffffff; border: 2px solid #e5e7eb;">
                <div class="space-y-4">
                    {{-- ヘッダー --}}
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-lg line-clamp-2 group-hover:text-blue-600 transition-colors"
                                style="color: #000000;">
                                {{ $insight->title }}
                            </h3>
                        </div>

                        @php
                            $priorityConfig = match ($insight->priority->value) {
                                'high' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => '高'],
                                'medium' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'label' => '中'],
                                'low' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => '低'],
                            };
                        @endphp
                        <span
                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $priorityConfig['bg'] }} {{ $priorityConfig['text'] }}">
                            {{ $priorityConfig['label'] }}
                        </span>
                    </div>

                    {{-- カテゴリ --}}
                    <div>
                        @php
                            $categoryConfig = match ($insight->category->value) {
                                'performance' => ['label' => 'パフォーマンス', 'bg' => '#3b82f6', 'text' => '#ffffff'],
                                'budget' => ['label' => '予算', 'bg' => '#f59e0b', 'text' => '#ffffff'],
                                'targeting' => ['label' => 'ターゲティング', 'bg' => '#8b5cf6', 'text' => '#ffffff'],
                                'creative' => ['label' => 'クリエイティブ', 'bg' => '#ec4899', 'text' => '#ffffff'],
                                'conversion' => ['label' => 'コンバージョン', 'bg' => '#10b981', 'text' => '#ffffff'],
                            };
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                            style="background-color: {{ $categoryConfig['bg'] }}; color: {{ $categoryConfig['text'] }};">
                            {{ $categoryConfig['label'] }}
                        </span>
                    </div>

                    {{-- 説明 --}}
                    <p class="text-sm line-clamp-3" style="color: #000000;">
                        {{ $insight->description }}
                    </p>

                    {{-- スコア --}}
                    <div class="flex items-center gap-4 pt-4 border-t border-gray-200">
                        <div class="flex items-center gap-2 text-sm">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span style="color: #000000;">インパクト:</span>
                            <span class="font-bold" style="color: #000000;">{{ $insight->impact_score }}/10</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span style="color: #000000;">信頼度:</span>
                            <span class="font-bold"
                                style="color: #000000;">{{ number_format($insight->confidence_score * 100) }}%</span>
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full">
                <div class="card">
                    <div class="text-center py-16 text-gray-500">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-6">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M6.343 6.343l-.707.707m12.728 0l-.707.707M6.343 17.657l-.707.707M17.657 6.343l-.707-.707m-12.728 0l-.707.707m12.728 12.728l-.707.707M17.657 17.657l-.707-.707M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">インサイトがありません</h3>
                        <p class="text-gray-500 mb-6">レポートを生成して、AIによる分析結果を確認しましょう</p>
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
    @if ($insights->hasPages())
        <div class="card p-4">
            {{ $insights->links() }}
        </div>
    @endif
</div>
