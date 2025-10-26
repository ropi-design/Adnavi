<?php

use App\Models\Insight;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use function Livewire\Volt\{with};

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $priorityFilter = 'all';
    public string $categoryFilter = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPriorityFilter(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $query = Insight::query()
            ->whereHas('analysisReport', fn($q) => $q->where('user_id', Auth::id()))
            ->with(['analysisReport.adAccount']);

        // 検索
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")->orWhere('description', 'like', "%{$this->search}%");
            });
        }

        // 優先度フィルター
        if ($this->priorityFilter !== 'all') {
            $query->where('priority', $this->priorityFilter);
        }

        // カテゴリフィルター
        if ($this->categoryFilter !== 'all') {
            $query->where('category', $this->categoryFilter);
        }

        // 優先度順にソート
        $query->orderByRaw('CASE priority 
            WHEN "high" THEN 1 
            WHEN "medium" THEN 2 
            WHEN "low" THEN 3 
        END');

        return [
            'insights' => $query->paginate(12),
        ];
    }
}; ?>

<div class="space-y-6">
    <flux:heading size="lg">インサイト</flux:heading>

    {{-- フィルター --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="col-span-2">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="検索..." icon="magnifying-glass" />
        </div>

        <flux:select wire:model.live="priorityFilter">
            <option value="all">全ての優先度</option>
            <option value="high">高</option>
            <option value="medium">中</option>
            <option value="low">低</option>
        </flux:select>

        <flux:select wire:model.live="categoryFilter">
            <option value="all">全てのカテゴリ</option>
            <option value="performance">パフォーマンス</option>
            <option value="budget">予算</option>
            <option value="targeting">ターゲティング</option>
            <option value="creative">クリエイティブ</option>
            <option value="conversion">コンバージョン</option>
        </flux:select>
    </div>

    {{-- インサイト一覧 --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($insights as $insight)
            <flux:card class="hover:shadow-lg transition-shadow cursor-pointer" href="/insights/{{ $insight->id }}"
                wire:navigate>
                <div class="space-y-3">
                    {{-- バッジとタイトル --}}
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1">
                            <h3 class="font-semibold text-lg line-clamp-2">
                                {{ $insight->title }}
                            </h3>
                        </div>

                        @php
                            $badgeColor = match ($insight->priority) {
                                'high' => 'danger',
                                'medium' => 'warning',
                                'low' => 'neutral',
                            };
                        @endphp
                        <flux:badge :variant="$badgeColor" size="sm">
                            {{ match ($insight->priority) {
                                'high' => '高',
                                'medium' => '中',
                                'low' => '低',
                            } }}
                        </flux:badge>
                    </div>

                    {{-- カテゴリ --}}
                    <div>
                        <flux:badge variant="ghost" size="sm">
                            {{ match ($insight->category) {
                                'performance' => 'パフォーマンス',
                                'budget' => '予算',
                                'targeting' => 'ターゲティング',
                                'creative' => 'クリエイティブ',
                                'conversion' => 'コンバージョン',
                            } }}
                        </flux:badge>
                    </div>

                    {{-- 説明 --}}
                    <p class="text-sm text-gray-600 line-clamp-3">
                        {{ $insight->description }}
                    </p>

                    {{-- スコア --}}
                    <div class="flex items-center gap-4 pt-2 border-t border-gray-200">
                        <div class="flex items-center gap-1 text-sm">
                            <span class="text-gray-500">インパクト:</span>
                            <span class="font-semibold">{{ $insight->impact_score }}/10</span>
                        </div>
                        <div class="flex items-center gap-1 text-sm">
                            <span class="text-gray-500">信頼度:</span>
                            <span class="font-semibold">{{ number_format($insight->confidence_score * 100) }}%</span>
                        </div>
                    </div>
                </div>
            </flux:card>
        @empty
            <div class="col-span-full">
                <flux:card>
                    <div class="text-center py-12 text-gray-500">
                        <flux:icon.light-bulb class="w-12 h-12 mx-auto mb-4 opacity-50" />
                        <p>インサイトがありません</p>
                        <p class="text-sm mt-2">レポートを生成して、AIによる分析結果を確認しましょう</p>
                    </div>
                </flux:card>
            </div>
        @endforelse
    </div>

    {{-- ページネーション --}}
    <div>
        {{ $insights->links() }}
    </div>
</div>
