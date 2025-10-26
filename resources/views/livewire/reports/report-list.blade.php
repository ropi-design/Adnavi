<?php

use App\Models\AnalysisReport;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Livewire\Volt\Component;
use function Livewire\Volt\{with};

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function sortByColumn($column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function with(): array
    {
        $query = AnalysisReport::query()
            ->where('user_id', Auth::id())
            ->with(['adAccount']);

        // 検索
        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('adAccount', fn($q) => $q->where('account_name', 'like', "%{$this->search}%"))->orWhere('report_type', 'like', "%{$this->search}%");
            });
        }

        // ステータスフィルター
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        // ソート
        $query->orderBy($this->sortBy, $this->sortDirection);

        return [
            'reports' => $query->paginate(10),
        ];
    }
}; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <flux:heading size="lg">分析レポート</flux:heading>

        <flux:button href="/reports/generate" wire:navigate icon="plus">
            新規作成
        </flux:button>
    </div>

    {{-- フィルター --}}
    <div class="flex gap-4">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="検索..." icon="magnifying-glass" />
        </div>

        <flux:select wire:model.live="statusFilter">
            <option value="all">全てのステータス</option>
            <option value="pending">待機中</option>
            <option value="processing">処理中</option>
            <option value="completed">完了</option>
            <option value="failed">失敗</option>
        </flux:select>
    </div>

    {{-- レポート一覧 --}}
    <div class="space-y-3">
        @forelse($reports as $report)
            <flux:card class="hover:shadow-lg transition-shadow">
                <div class="flex items-start gap-4">
                    {{-- ステータスバッジ --}}
                    <div>
                        @php
                            $variant = match ($report->status) {
                                'completed' => 'success',
                                'processing' => 'info',
                                'failed' => 'danger',
                                default => 'neutral',
                            };
                        @endphp
                        <flux:badge :variant="$variant">
                            {{ match ($report->status) {
                                'pending' => '待機中',
                                'processing' => '処理中',
                                'completed' => '完了',
                                'failed' => '失敗',
                            } }}
                        </flux:badge>
                    </div>

                    {{-- レポート情報 --}}
                    <div class="flex-1">
                        <h3 class="font-semibold text-lg">
                            {{ $report->adAccount->account_name }}
                        </h3>

                        <div class="text-sm text-gray-600 mt-1">
                            {{ match ($report->report_type) {
                                'daily' => '日次レポート',
                                'weekly' => '週次レポート',
                                'monthly' => '月次レポート',
                                'custom' => 'カスタムレポート',
                            } }}
                            |
                            {{ \Carbon\Carbon::parse($report->start_date)->isoFormat('YYYY/MM/DD') }}
                            〜
                            {{ \Carbon\Carbon::parse($report->end_date)->isoFormat('YYYY/MM/DD') }}
                        </div>

                        <div class="text-xs text-gray-500 mt-2">
                            作成: {{ $report->created_at->diffForHumans() }}
                        </div>
                    </div>

                    {{-- アクション --}}
                    <div class="flex gap-2">
                        @if ($report->status === 'completed')
                            <flux:button variant="primary" size="sm" href="/reports/{{ $report->id }}"
                                wire:navigate>
                                詳細
                            </flux:button>
                        @endif

                        @if ($report->status === 'failed')
                            <flux:button variant="ghost" size="sm">
                                再試行
                            </flux:button>
                        @endif
                    </div>
                </div>
            </flux:card>
        @empty
            <flux:card>
                <div class="text-center py-12 text-gray-500">
                    <flux:icon.document-text class="w-12 h-12 mx-auto mb-4 opacity-50" />
                    <p>レポートがありません</p>
                    <flux:button href="/reports/generate" wire:navigate class="mt-4">
                        最初のレポートを作成
                    </flux:button>
                </div>
            </flux:card>
        @endforelse
    </div>

    {{-- ページネーション --}}
    <div>
        {{ $reports->links() }}
    </div>
</div>
