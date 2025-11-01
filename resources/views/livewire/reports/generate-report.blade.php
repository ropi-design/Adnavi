<?php

use App\Models\AdAccount;
use App\Models\AnalyticsProperty;
use App\Models\AnalysisReport;
use App\Jobs\GenerateAnalysisReport;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\Attributes\Validate;

new class extends Component {
    #[Validate('required')]
    public string $reportType = 'weekly';

    #[Validate('required|exists:ad_accounts,id')]
    public ?int $adAccountId = null;

    #[Validate('nullable|exists:analytics_properties,id')]
    public ?int $analyticsPropertyId = null;

    #[Validate('required|date')]
    public string $startDate = '';

    #[Validate('required|date|after:start_date')]
    public string $endDate = '';

    public $adAccounts = [];
    public $analyticsProperties = [];
    public bool $isGenerating = false;

    public function mount(): void
    {
        $user = Auth::user();

        $this->adAccounts = AdAccount::where('user_id', $user->id)->where('is_active', true)->get();
        $this->analyticsProperties = AnalyticsProperty::where('user_id', $user->id)->where('is_active', true)->get();

        $this->startDate = now()->subWeek()->startOfWeek()->format('Y-m-d');
        $this->endDate = now()->subWeek()->endOfWeek()->format('Y-m-d');

        if ($this->adAccounts->isNotEmpty()) {
            $this->adAccountId = $this->adAccounts->first()->id;
        }
    }

    public function updatedReportType($value): void
    {
        match ($value) {
            'daily' => $this->setDailyPeriod(),
            'weekly' => $this->setWeeklyPeriod(),
            'monthly' => $this->setMonthlyPeriod(),
            default => null,
        };
    }

    public function setDailyPeriod(): void
    {
        $this->startDate = now()->subDay()->format('Y-m-d');
        $this->endDate = now()->subDay()->format('Y-m-d');
    }

    public function setWeeklyPeriod(): void
    {
        $this->startDate = now()->subWeek()->startOfWeek()->format('Y-m-d');
        $this->endDate = now()->subWeek()->endOfWeek()->format('Y-m-d');
    }

    public function setMonthlyPeriod(): void
    {
        $this->startDate = now()->subMonth()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->subMonth()->endOfMonth()->format('Y-m-d');
    }

    public function generate(): void
    {
        $this->validate();

        $this->isGenerating = true;

        try {
            // レポートレコードを作成
            $report = AnalysisReport::create([
                'user_id' => Auth::id(),
                'ad_account_id' => $this->adAccountId,
                'analytics_property_id' => $this->analyticsPropertyId,
                'report_type' => $this->reportType,
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'status' => 'pending',
            ]);

            // ジョブをディスパッチ（ローカル/同期時は即実行して待機を回避）
            if (config('queue.default', 'sync') === 'sync' || app()->environment('local')) {
                GenerateAnalysisReport::dispatchSync($report->id);
            } else {
                GenerateAnalysisReport::dispatch($report->id);
            }

            session()->flash('message', 'レポート生成を開始しました。同期実行の場合は即時に結果が反映されます。');
            $this->redirect('/reports', navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', 'レポート生成の開始に失敗しました: ' . $e->getMessage());
            $this->isGenerating = false;
        }
    }
}; ?>

<div class="p-6 lg:p-8 animate-fade-in">
    <div class="max-w-4xl mx-auto">
        {{-- ヘッダー --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-white mb-1">AIレポート生成</h1>
            <p class="text-sm text-white">Geminiで効果分析を自動実行</p>
        </div>

        <form wire:submit="generate" class="card p-8 space-y-6">
            {{-- レポートタイプ --}}
            <div>
                <label class="block text-sm font-bold text-white mb-3">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    レポートタイプ
                </label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <label class="relative">
                        <input type="radio" wire:model.live="reportType" value="daily" class="peer sr-only" />
                        <div
                            class="p-4 border-2 rounded-xl cursor-pointer transition-all peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 border-gray-200 hover:border-blue-400 peer-checked:shadow-lg text-white">
                            <div class="text-center">
                                <div class="font-bold text-lg">日次</div>
                                <div class="text-xs mt-1">昨日のデータ</div>
                            </div>
                        </div>
                    </label>
                    <label class="relative">
                        <input type="radio" wire:model.live="reportType" value="weekly" class="peer sr-only" />
                        <div
                            class="p-4 border-2 rounded-xl cursor-pointer transition-all peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 border-gray-200 hover:border-blue-400 peer-checked:shadow-lg text-white">
                            <div class="text-center">
                                <div class="font-bold text-lg">週次</div>
                                <div class="text-xs mt-1">先週のデータ</div>
                            </div>
                        </div>
                    </label>
                    <label class="relative">
                        <input type="radio" wire:model.live="reportType" value="monthly" class="peer sr-only" />
                        <div
                            class="p-4 border-2 rounded-xl cursor-pointer transition-all peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 border-gray-200 hover:border-blue-400 peer-checked:shadow-lg text-white">
                            <div class="text-center">
                                <div class="font-bold text-lg">月次</div>
                                <div class="text-xs mt-1">先月のデータ</div>
                            </div>
                        </div>
                    </label>
                    <label class="relative">
                        <input type="radio" wire:model.live="reportType" value="custom" class="peer sr-only" />
                        <div
                            class="p-4 border-2 rounded-xl cursor-pointer transition-all peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 border-gray-200 hover:border-blue-400 peer-checked:shadow-lg text-white">
                            <div class="text-center">
                                <div class="font-bold text-lg">カスタム</div>
                                <div class="text-xs mt-1">期間を指定</div>
                            </div>
                        </div>
                    </label>
                </div>
                @error('reportType')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- 広告アカウント --}}
            <div>
                <label class="block text-sm font-bold text-white mb-2">広告アカウント *</label>
                <select wire:model="adAccountId" class="form-input"
                    style="background-color: white !important; color: #111827 !important;">
                    <option value="">選択してください</option>
                    @foreach ($adAccounts as $account)
                        <option value="{{ $account->id }}">{{ $account->account_name }}</option>
                    @endforeach
                </select>
                @error('adAccountId')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Analyticsプロパティ --}}
            <div>
                <label class="block text-sm font-bold text-white mb-2">
                    Analyticsプロパティ
                    <span class="text-xs text-white font-normal">（オプション）</span>
                </label>
                <select wire:model="analyticsPropertyId" class="form-input"
                    style="background-color: white !important; color: #111827 !important;">
                    <option value="">選択しない</option>
                    @foreach ($analyticsProperties as $property)
                        <option value="{{ $property->id }}">{{ $property->property_name }}</option>
                    @endforeach
                </select>
                <p class="text-sm text-white mt-2">Analyticsデータも含めて分析する場合は選択してください</p>
            </div>

            {{-- 期間 --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-white mb-2">開始日</label>
                    <input type="date" wire:model="startDate" {{ $reportType !== 'custom' ? 'disabled' : '' }}
                        class="form-input {{ $reportType !== 'custom' ? 'opacity-50 cursor-not-allowed' : '' }}"
                        style="background-color: white !important; color: #111827 !important;" />
                    @error('startDate')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-bold text-white mb-2">終了日</label>
                    <input type="date" wire:model="endDate" {{ $reportType !== 'custom' ? 'disabled' : '' }}
                        class="form-input {{ $reportType !== 'custom' ? 'opacity-50 cursor-not-allowed' : '' }}"
                        style="background-color: white !important; color: #111827 !important;" />
                    @error('endDate')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- プレビュー --}}
            @if ($startDate && $endDate)
                <div class="p-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-xl">
                    <div class="flex items-center gap-3 text-gray-700">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <strong>分析期間:</strong>
                            {{ \Carbon\Carbon::parse($startDate)->isoFormat('YYYY年MM月DD日') }}
                            〜
                            {{ \Carbon\Carbon::parse($endDate)->isoFormat('YYYY年MM月DD日') }}
                            <span
                                class="ml-2 text-sm">({{ \Carbon\Carbon::parse($startDate)->diffInDays($endDate) + 1 }}日間)</span>
                        </div>
                    </div>
                </div>
            @endif

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

            @if (session('error'))
                <div class="p-4 bg-red-100 border-l-4 border-red-500 rounded-lg">
                    <div class="flex items-center gap-2 text-red-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        {{ session('error') }}
                    </div>
                </div>
            @endif

            {{-- ボタン --}}
            <div class="flex gap-3 pt-4 border-t border-gray-200">
                <button type="submit" wire:loading.attr="disabled"
                    class="btn btn-primary flex items-center gap-2 flex-1 justify-center"
                    wire:loading.class="opacity-50" wire:loading.attr="disabled">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        wire:loading.class="animate-spin" wire:target="generate">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                    <span wire:loading.remove wire:target="generate">AIレポート生成</span>
                    <span wire:loading wire:target="generate">生成中...</span>
                </button>

                <a href="/reports" class="btn btn-secondary inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    キャンセル
                </a>
            </div>
        </form>
    </div>
</div>
