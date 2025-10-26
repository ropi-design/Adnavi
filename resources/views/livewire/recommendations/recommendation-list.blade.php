<?php

use App\Models\Recommendation;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use function Livewire\Volt\{with};

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';
    public string $difficultyFilter = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDifficultyFilter(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $query = Recommendation::query()
            ->whereHas('insight.analysisReport', fn($q) => $q->where('user_id', Auth::id()))
            ->with(['insight.analysisReport.adAccount']);

        // 検索
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")->orWhere('description', 'like', "%{$this->search}%");
            });
        }

        // ステータスフィルター
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        // 難易度フィルター
        if ($this->difficultyFilter !== 'all') {
            $query->where('implementation_difficulty', $this->difficultyFilter);
        }

        // 優先度順にソート
        $query
            ->orderByRaw(
                'CASE status 
            WHEN "pending" THEN 1 
            WHEN "in_progress" THEN 2 
            WHEN "implemented" THEN 3 
            WHEN "dismissed" THEN 4 
        END',
            )
            ->orderBy('created_at', 'desc');

        return [
            'recommendations' => $query->paginate(12),
        ];
    }
}; ?>

<div class="space-y-6">
    <flux:heading size="lg">改善施策</flux:heading>

    {{-- フィルター --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="col-span-2">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="検索..." icon="magnifying-glass" />
        </div>

        <flux:select wire:model.live="statusFilter">
            <option value="all">全てのステータス</option>
            <option value="pending">未着手</option>
            <option value="in_progress">実施中</option>
            <option value="implemented">実施済み</option>
            <option value="dismissed">却下</option>
        </flux:select>

        <flux:select wire:model.live="difficultyFilter">
            <option value="all">全ての難易度</option>
            <option value="easy">簡単</option>
            <option value="medium">普通</option>
            <option value="hard">難しい</option>
        </flux:select>
    </div>

    {{-- 施策一覧 --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @forelse($recommendations as $recommendation)
            <flux:card class="hover:shadow-lg transition-shadow">
                <div class="space-y-4">
                    {{-- ヘッダー --}}
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="font-semibold text-lg flex-1">
                            {{ $recommendation->title }}
                        </h3>

                        @php
                            $badgeColor = match ($recommendation->status) {
                                'pending' => 'neutral',
                                'in_progress' => 'info',
                                'implemented' => 'success',
                                'dismissed' => 'ghost',
                            };
                        @endphp
                        <flux:badge :variant="$badgeColor" size="sm">
                            {{ match ($recommendation->status) {
                                'pending' => '未着手',
                                'in_progress' => '実施中',
                                'implemented' => '実施済み',
                                'dismissed' => '却下',
                            } }}
                        </flux:badge>
                    </div>

                    {{-- 説明 --}}
                    <p class="text-sm text-gray-600 line-clamp-3">
                        {{ $recommendation->description }}
                    </p>

                    {{-- メタ情報 --}}
                    <div class="flex items-center gap-4 text-sm">
                        <div class="flex items-center gap-1">
                            <flux:icon.rocket-launch class="w-4 h-4 text-gray-400" />
                            <span class="text-gray-500">難易度:</span>
                            <span class="font-semibold">
                                {{ match ($recommendation->implementation_difficulty) {
                                    'easy' => '簡単',
                                    'medium' => '普通',
                                    'hard' => '難しい',
                                } }}
                            </span>
                        </div>

                        @if ($recommendation->estimated_impact)
                            <div class="flex items-center gap-1">
                                <flux:icon.trending-up class="w-4 h-4 text-gray-400" />
                                <span class="text-gray-500">効果:</span>
                                <span class="font-semibold">{{ $recommendation->estimated_impact }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- アクション --}}
                    <div class="flex gap-2 pt-2 border-t border-gray-200">
                        <flux:button href="/recommendations/{{ $recommendation->id }}" wire:navigate size="sm">
                            詳細を見る
                        </flux:button>

                        @if ($recommendation->status === 'pending')
                            <flux:button variant="ghost" size="sm">
                                実施開始
                            </flux:button>
                        @endif
                    </div>
                </div>
            </flux:card>
        @empty
            <div class="col-span-full">
                <flux:card>
                    <div class="text-center py-12 text-gray-500">
                        <flux:icon.sparkles class="w-12 h-12 mx-auto mb-4 opacity-50" />
                        <p>改善施策がありません</p>
                        <p class="text-sm mt-2">レポートを生成して、AIによる提案を確認しましょう</p>
                    </div>
                </flux:card>
            </div>
        @endforelse
    </div>

    {{-- ページネーション --}}
    <div>
        {{ $recommendations->links() }}
    </div>
</div>
