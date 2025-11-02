<?php

use App\Models\AnalysisReport;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{state, mount};

state([
    'report' => null,
    'loading' => false,
    'showDeleteConfirm' => false,
]);

mount(function ($id) {
    $this->loadReport($id);
});

$loadReport = function ($id) {
    $this->loading = true;

    $this->report = AnalysisReport::with(['adAccount', 'analyticsProperty', 'insights.recommendations'])
        ->where('user_id', Auth::id())
        ->findOrFail($id);

    $this->loading = false;
};

$showDeleteConfirm = function () {
    $this->showDeleteConfirm = true;
};

$cancelDelete = function () {
    $this->showDeleteConfirm = false;
};

$deleteReport = function () {
    $this->report->delete();
    session()->flash('message', 'レポートを削除しました');

    $this->redirect('/reports', navigate: true);
};

?>

<div class="p-6 lg:p-8 space-y-6 animate-fade-in">
    {{-- ローディング --}}
    <div wire:loading wire:target="loadReport" class="flex flex-col items-center justify-center py-16">
        <svg class="w-12 h-12 text-blue-600 animate-spin mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        <p class="text-white font-medium">レポートを読み込んでいます...</p>
    </div>

    @if ($report && !$loading)
        {{-- ヘッダー --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-4xl font-bold text-white">分析レポートの{{ $report->adAccount->account_name }}</h1>
                <p class="text-white mt-2">
                    {{ match ($report->report_type->value) {
                        'daily' => '日次レポート',
                        'weekly' => '週次レポート',
                        'monthly' => '月次レポート',
                        'custom' => 'カスタムレポート',
                    } }}
                    | {{ \Carbon\Carbon::parse($report->start_date)->isoFormat('YYYY年MM月DD日') }}
                    〜
                    {{ \Carbon\Carbon::parse($report->end_date)->isoFormat('YYYY年MM月DD日') }}
                </p>
            </div>

            <div class="flex gap-3">
                @php
                    $statusConfig = match ($report->status->value) {
                        'completed' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'label' => '完了'],
                        'processing' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'label' => '処理中'],
                        'failed' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => '失敗'],
                        default => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => '待機中'],
                    };
                @endphp
                <span
                    class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-bold {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                    {{ $statusConfig['label'] }}
                </span>

                <button wire:click="showDeleteConfirm"
                    class="btn bg-white text-black border-2 border-white hover:bg-gray-100 hover:border-gray-300">
                    削除
                </button>
            </div>
        </div>

        @if ($report->status->value === 'completed')
            {{-- 概要 --}}
            <div class="card p-6">
                <h2 class="text-2xl font-bold text-white mb-4">概要</h2>
                <div class="space-y-2 text-white">
                    <p><strong>作成日:</strong> {{ $report->created_at->isoFormat('YYYY年MM月DD日 HH:mm') }}</p>
                    <p><strong>期間:</strong> {{ $report->start_date->isoFormat('YYYY/MM/DD') }} 〜
                        {{ $report->end_date->isoFormat('YYYY/MM/DD') }}</p>
                    @if ($report->analyticsProperty)
                        <p><strong>Analytics:</strong> {{ $report->analyticsProperty->property_name }}を含む</p>
                    @endif
                </div>
            </div>

            {{-- インサイト一覧 --}}
            @if ($report->insights->count() > 0)
                <div class="card p-6">
                    <h2 class="text-2xl font-bold text-white mb-6">抽出されたインサイト</h2>

                    <div class="space-y-4">
                        @foreach ($report->insights as $insight)
                            <div
                                class="p-6 border-2 border-gray-200 rounded-xl hover:border-blue-300 hover:shadow-lg transition-all">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-3">
                                            @php
                                                $priorityConfig = match ($insight->priority->value) {
                                                    'high' => [
                                                        'bg' => 'bg-red-100',
                                                        'text' => 'text-red-800',
                                                        'label' => '高',
                                                    ],
                                                    'medium' => [
                                                        'bg' => 'bg-yellow-100',
                                                        'text' => 'text-yellow-800',
                                                        'label' => '中',
                                                    ],
                                                    'low' => [
                                                        'bg' => 'bg-gray-100',
                                                        'text' => 'text-gray-800',
                                                        'label' => '低',
                                                    ],
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
                                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $priorityConfig['bg'] }} {{ $priorityConfig['text'] }}">
                                                {{ $priorityConfig['label'] }}
                                            </span>
                                            <span class="text-sm text-white">{{ $categoryLabel }}</span>
                                        </div>

                                        <h4 class="font-bold text-xl text-white mb-2">{{ $insight->title }}</h4>
                                        <p class="text-white mb-4 leading-relaxed">{{ $insight->description }}</p>

                                        @if ($insight->data_points)
                                            @php
                                                $dataPoints = is_array($insight->data_points)
                                                    ? $insight->data_points
                                                    : json_decode($insight->data_points, true);
                                            @endphp
                                            @if ($dataPoints)
                                                <div class="p-4 bg-blue-50 rounded-lg mb-4">
                                                    <h5 class="font-bold text-sm mb-3" style="color: #1e40af;">データポイント
                                                    </h5>
                                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                                        @if (isset($dataPoints['current_value']))
                                                            <div>
                                                                <span class="text-gray-600">現在の値:</span>
                                                                <span class="font-bold ml-2" style="color: #1e40af;">
                                                                    @if (is_numeric($dataPoints['current_value']))
                                                                        @if ($dataPoints['current_value'] >= 1000)
                                                                            {{ number_format($dataPoints['current_value']) }}
                                                                        @else
                                                                            {{ number_format($dataPoints['current_value'], 2) }}
                                                                        @endif
                                                                    @else
                                                                        {{ $dataPoints['current_value'] }}
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        @endif
                                                        @if (isset($dataPoints['target_value']))
                                                            <div>
                                                                <span class="text-gray-600">目標値:</span>
                                                                <span class="font-bold ml-2" style="color: #16a34a;">
                                                                    @if (is_numeric($dataPoints['target_value']))
                                                                        @if ($dataPoints['target_value'] >= 1000)
                                                                            {{ number_format($dataPoints['target_value']) }}
                                                                        @else
                                                                            {{ number_format($dataPoints['target_value'], 2) }}
                                                                        @endif
                                                                    @else
                                                                        {{ $dataPoints['target_value'] }}
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        @endif
                                                        @if (isset($dataPoints['benchmark']))
                                                            <div>
                                                                <span class="text-gray-600">ベンチマーク:</span>
                                                                <span class="font-bold ml-2" style="color: #6b7280;">
                                                                    @if (is_numeric($dataPoints['benchmark']))
                                                                        @if ($dataPoints['benchmark'] >= 1000)
                                                                            {{ number_format($dataPoints['benchmark']) }}
                                                                        @else
                                                                            {{ number_format($dataPoints['benchmark'], 2) }}
                                                                        @endif
                                                                    @else
                                                                        {{ $dataPoints['benchmark'] }}
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        @endif
                                                        @if (isset($dataPoints['affected_metrics']) && is_array($dataPoints['affected_metrics']))
                                                            <div class="col-span-2">
                                                                <span class="text-gray-600">影響を受ける指標:</span>
                                                                <div class="flex flex-wrap gap-2 mt-1">
                                                                    @foreach ($dataPoints['affected_metrics'] as $metric)
                                                                        <span
                                                                            class="px-2 py-1 bg-blue-100 rounded text-xs font-semibold"
                                                                            style="color: #1e40af;">
                                                                            {{ $metric }}
                                                                        </span>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        @endif

                                        <div class="flex items-center gap-6 mt-4 text-sm">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                </svg>
                                                <span class="text-white">インパクト:</span>
                                                @php
                                                    $impactLabel = match (true) {
                                                        $insight->impact_score >= 8 => [
                                                            'label' => '大',
                                                            'bg' => 'bg-red-100',
                                                            'text' => 'text-red-800',
                                                        ],
                                                        $insight->impact_score >= 4 => [
                                                            'label' => '中',
                                                            'bg' => 'bg-yellow-100',
                                                            'text' => 'text-yellow-800',
                                                        ],
                                                        default => [
                                                            'label' => '小',
                                                            'bg' => 'bg-gray-100',
                                                            'text' => 'text-gray-800',
                                                        ],
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
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span class="text-white">信頼度:</span>
                                                <span
                                                    class="font-bold text-white">{{ number_format($insight->confidence_score * 100) }}%</span>
                                            </div>
                                            @if ($insight->recommendations && $insight->recommendations->count() > 0)
                                                <div class="flex items-center gap-2">
                                                    <svg class="w-4 h-4 text-purple-600" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                                    </svg>
                                                    <span class="text-white">改善施策:</span>
                                                    <span
                                                        class="font-bold text-white">{{ $insight->recommendations->count() }}件</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-2">
                                        <a href="/insights/{{ $insight->id }}" class="btn btn-primary text-sm">
                                            詳細
                                        </a>
                                        @if ($insight->recommendations && $insight->recommendations->count() > 0)
                                            <a href="/insights/{{ $insight->id }}#recommendations"
                                                class="btn text-sm inline-flex items-center justify-center gap-2"
                                                style="background-color: #9333ea; color: #ffffff; border: 2px solid #9333ea;">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                                </svg>
                                                改善施策を見る ({{ $insight->recommendations->count() }})
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- 改善施策一覧 --}}
            @if ($report->recommendations->count() > 0)
                <div class="card p-6">
                    <h2 class="text-2xl font-bold text-white mb-6">改善施策</h2>
                    <div class="space-y-4">
                        @foreach ($report->recommendations as $recommendation)
                            <div class="p-6 border-2 border-gray-200 rounded-xl hover:border-blue-300 hover:shadow-lg transition-all"
                                style="background-color: rgba(255, 255, 255, 0.05);">
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
                                                $difficultyLabel = match ($recommendation->implementation_difficulty) {
                                                    'easy' => '簡単',
                                                    'medium' => '普通',
                                                    'hard' => '難しい',
                                                };
                                            @endphp
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                                                {{ $statusConfig['label'] }}
                                            </span>
                                            <span class="text-sm text-white">難易度: {{ $difficultyLabel }}</span>
                                        </div>

                                        <h4 class="font-bold text-xl text-white mb-2">{{ $recommendation->title }}
                                        </h4>
                                        <p class="text-white mb-4">{{ $recommendation->description }}</p>

                                        @if ($recommendation->estimated_impact)
                                            <div class="p-4 bg-blue-50 rounded-lg mb-4">
                                                @php
                                                    $impactLines = explode(' | ', $recommendation->estimated_impact);
                                                @endphp
                                                <div class="space-y-2">
                                                    @foreach ($impactLines as $line)
                                                        <div class="flex items-start gap-2">
                                                            <svg class="w-4 h-4 text-blue-600 flex-shrink-0 mt-1"
                                                                fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                                            </svg>
                                                            <p class="text-sm font-semibold" style="color: #1e40af;">
                                                                {{ $line }}</p>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        @if ($recommendation->specific_actions && count($recommendation->specific_actions) > 0)
                                            <div class="mt-4">
                                                <p class="text-sm font-semibold text-white mb-2">実施手順:</p>
                                                <ul class="space-y-2">
                                                    @foreach ($recommendation->specific_actions as $index => $action)
                                                        <li class="flex items-start gap-2 text-sm text-white">
                                                            <span
                                                                class="flex-shrink-0 w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-bold mt-0.5">
                                                                {{ $index + 1 }}
                                                            </span>
                                                            <span>{{ $action }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </div>

                                    <a href="/recommendations/{{ $recommendation->id }}"
                                        class="btn btn-primary text-sm" style="color: #000000 !important;">
                                        詳細
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @elseif($report->status->value === 'failed')
            <div class="card p-6">
                <div class="p-6 bg-red-50 border-l-4 border-red-500 rounded-lg overflow-hidden">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-red-600 flex-shrink-0 mt-1" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="flex-1 min-w-0 overflow-hidden">
                            <p class="font-bold text-red-900">レポート生成に失敗しました</p>
                            <p class="mt-2 text-sm text-red-800 break-all">
                                {{ $report->error_message }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="card">
                <div class="text-center py-16">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-100 rounded-full mb-6">
                        <svg class="w-10 h-10 text-blue-600 animate-spin" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-xl font-semibold text-white">レポートを生成中です...</p>
                    <p class="text-white mt-2">完了次第、通知いたします</p>
                </div>
            </div>
        @endif
    @endif

    {{-- 削除確認モーダル --}}
    @if ($showDeleteConfirm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">レポートを削除</h3>
                <p class="text-gray-700 mb-6">
                    本当にこのレポートを削除しますか？この操作は取り消せません。
                </p>
                <div class="flex gap-3 justify-end">
                    <button wire:click="cancelDelete" class="btn bg-gray-100 text-gray-700 hover:bg-gray-200">
                        キャンセル
                    </button>
                    <button wire:click="deleteReport" class="btn bg-red-600 text-white hover:bg-red-700">
                        削除
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
