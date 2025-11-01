# Livewire Volt 実装ガイド

## Livewire Volt とは

Livewire Volt は、Laravel の Livewire をベースにしたシングルファイルコンポーネント（SFC）フレームワークです。
Vue.js の SFC のように、ロジックとビューを 1 つのファイルに記述できます。

### Volt の特徴

-   ✅ **シングルファイル**: ロジックとビューが同じファイル
-   ✅ **関数ベース**: シンプルで直感的な記述
-   ✅ **リアクティブ**: データが自動的に同期
-   ✅ **型安全**: PHP 8.3+ の型システムを活用
-   ✅ **Flux との統合**: 美しい UI コンポーネント

## Volt のセットアップ

### 1. インストール（既にインストール済み）

```bash
composer require livewire/volt
```

### 2. Volt の設定

`config/livewire.php` または `config/volt.php` の確認：

```php
<?php

return [
    // Voltコンポーネントの配置場所
    'mount_paths' => [
        resource_path('views/livewire'),
    ],
];
```

### 3. ディレクトリ構造

```
resources/
└── views/
    └── livewire/
        ├── dashboard/
        │   ├── overview.blade.php          # ダッシュボード概要
        │   ├── metrics-summary.blade.php   # メトリクスサマリー
        │   └── metrics-chart.blade.php     # チャート
        ├── accounts/
        │   ├── connect-google.blade.php    # Google連携
        │   ├── ad-account-list.blade.php   # 広告アカウント一覧
        │   └── analytics-property-list.blade.php
        ├── reports/
        │   ├── report-list.blade.php       # レポート一覧
        │   ├── report-detail.blade.php     # レポート詳細
        │   └── generate-report.blade.php   # レポート生成
        ├── insights/
        │   ├── insight-list.blade.php
        │   └── insight-detail.blade.php
        └── recommendations/
            ├── recommendation-list.blade.php
            └── recommendation-detail.blade.php
```

## Volt コンポーネントの基本構造

### パターン 1: 関数ベース API（推奨・シンプル）

```blade
<?php

use function Livewire\Volt\{state, computed};

// 状態の定義
state(['count' => 0]);

// 算出プロパティ
$doubledCount = computed(fn() => $this->count * 2);

// メソッド
$increment = fn() => $this->count++;
$decrement = fn() => $this->count--;

?>

<div>
    <h1>カウンター</h1>
    <p>カウント: {{ $count }}</p>
    <p>2倍: {{ $this->doubledCount }}</p>

    <div>
        <button wire:click="increment">+</button>
        <button wire:click="decrement">-</button>
    </div>
</div>
```

### パターン 2: クラスベース API（複雑なロジック向け）

```blade
<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Validate;

new class extends Component {
    #[Validate('required|min:3')]
    public string $name = '';

    #[Validate('required|email')]
    public string $email = '';

    public function save(): void
    {
        $this->validate();

        // 保存処理

        session()->flash('message', '保存しました');
    }
}; ?>

<div>
    <form wire:submit="save">
        <input type="text" wire:model="name" placeholder="名前">
        @error('name') <span class="error">{{ $message }}</span> @enderror

        <input type="email" wire:model="email" placeholder="メール">
        @error('email') <span class="error">{{ $message }}</span> @enderror

        <button type="submit">保存</button>
    </form>

    @if (session('message'))
        <div class="alert">{{ session('message') }}</div>
    @endif
</div>
```

## 実装例：Adnavi アプリケーション

### 1. ダッシュボード概要

**`resources/views/livewire/dashboard/overview.blade.php`**

```blade
<?php

use App\Models\AdAccount;
use App\Models\AnalysisReport;
use App\Models\Insight;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{state, computed, mount};

// マウント時の処理
mount(function () {
    $this->loadData();
});

// 状態
state([
    'selectedPeriod' => 'today',
    'metrics' => null,
    'recentInsights' => collect(),
]);

// データ読み込み
$loadData = function () {
    $user = Auth::user();

    // 主要メトリクスの取得
    $this->metrics = [
        'impressions' => 125000,
        'clicks' => 3500,
        'cost' => 85000,
        'conversions' => 145,
        'ctr' => 2.8,
        'cpa' => 586,
    ];

    // 最近のインサイト
    $this->recentInsights = Insight::query()
        ->whereHas('analysisReport', fn($q) => $q->where('user_id', $user->id))
        ->where('priority', 'high')
        ->latest()
        ->take(5)
        ->get();
};

// 期間変更
$changePeriod = function ($period) {
    $this->selectedPeriod = $period;
    $this->loadData();
};

// データ再読み込み
$refresh = function () {
    $this->loadData();
    session()->flash('message', 'データを更新しました');
};

?>

<div class="space-y-6">
    {{-- ヘッダー --}}
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">ダッシュボード</h1>

        <div class="flex items-center gap-4">
            {{-- 期間選択 --}}
            <flux:select wire:model.live="selectedPeriod">
                <option value="today">今日</option>
                <option value="yesterday">昨日</option>
                <option value="week">今週</option>
                <option value="month">今月</option>
            </flux:select>

            <flux:button wire:click="refresh" icon="arrow-path">
                更新
            </flux:button>
        </div>
    </div>

    {{-- 成功メッセージ --}}
    @if (session('message'))
        <flux:alert variant="success">
            {{ session('message') }}
        </flux:alert>
    @endif

    {{-- メトリクスサマリー --}}
    @if($metrics)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <flux:card>
                <div class="space-y-2">
                    <div class="text-sm text-gray-500">インプレッション</div>
                    <div class="text-3xl font-bold">
                        {{ number_format($metrics['impressions']) }}
                    </div>
                </div>
            </flux:card>

            <flux:card>
                <div class="space-y-2">
                    <div class="text-sm text-gray-500">クリック数</div>
                    <div class="text-3xl font-bold">
                        {{ number_format($metrics['clicks']) }}
                    </div>
                </div>
            </flux:card>

            <flux:card>
                <div class="space-y-2">
                    <div class="text-sm text-gray-500">費用</div>
                    <div class="text-3xl font-bold">
                        ¥{{ number_format($metrics['cost']) }}
                    </div>
                </div>
            </flux:card>

            <flux:card>
                <div class="space-y-2">
                    <div class="text-sm text-gray-500">コンバージョン</div>
                    <div class="text-3xl font-bold">
                        {{ number_format($metrics['conversions']) }}
                    </div>
                </div>
            </flux:card>

            <flux:card>
                <div class="space-y-2">
                    <div class="text-sm text-gray-500">CTR</div>
                    <div class="text-3xl font-bold">
                        {{ number_format($metrics['ctr'], 2) }}%
                    </div>
                </div>
            </flux:card>

            <flux:card>
                <div class="space-y-2">
                    <div class="text-sm text-gray-500">CPA</div>
                    <div class="text-3xl font-bold">
                        ¥{{ number_format($metrics['cpa']) }}
                    </div>
                </div>
            </flux:card>
        </div>
    @endif

    {{-- 重要なインサイト --}}
    <flux:card>
        <flux:heading size="lg">重要なインサイト</flux:heading>

        <div class="mt-4 space-y-3">
            @forelse($recentInsights as $insight)
                <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                    <flux:badge variant="warning">{{ $insight->priority }}</flux:badge>

                    <div class="flex-1">
                        <h4 class="font-semibold">{{ $insight->title }}</h4>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ $insight->description }}
                        </p>
                        <div class="text-xs text-gray-500 mt-2">
                            インパクト: {{ $insight->impact_score }}/10
                        </div>
                    </div>

                    <flux:button variant="ghost" size="sm" href="/insights/{{ $insight->id }}">
                        詳細
                    </flux:button>
                </div>
            @empty
                <p class="text-gray-500 text-center py-4">
                    インサイトがありません
                </p>
            @endforelse
        </div>
    </flux:card>
</div>
```

### 2. Google アカウント連携

**`resources/views/livewire/accounts/connect-google.blade.php`**

```blade
<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public bool $isConnected = false;
    public ?string $connectedEmail = null;

    public function mount(): void
    {
        $this->checkConnection();
    }

    public function checkConnection(): void
    {
        $googleAccount = Auth::user()->googleAccounts()->first();

        $this->isConnected = $googleAccount !== null;
        $this->connectedEmail = $googleAccount?->email;
    }

    public function connect(): void
    {
        // Google OAuth認証へリダイレクト
        $this->redirect('/auth/google');
    }

    public function disconnect(): void
    {
        Auth::user()->googleAccounts()->delete();

        $this->checkConnection();

        session()->flash('message', 'Googleアカウントの連携を解除しました');
    }
}; ?>

<flux:card>
    <flux:heading size="lg">Google アカウント連携</flux:heading>

    <div class="mt-6">
        @if($isConnected)
            {{-- 連携済み --}}
            <div class="space-y-4">
                <div class="flex items-center gap-3 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <flux:icon.check-circle class="w-6 h-6 text-green-600" />
                    <div class="flex-1">
                        <div class="font-semibold">連携済み</div>
                        <div class="text-sm text-gray-600">{{ $connectedEmail }}</div>
                    </div>
                </div>

                <div class="flex gap-2">
                    <flux:button
                        variant="danger"
                        wire:click="disconnect"
                        wire:confirm="連携を解除してもよろしいですか？"
                    >
                        連携解除
                    </flux:button>
                </div>
            </div>
        @else
            {{-- 未連携 --}}
            <div class="space-y-4">
                <p class="text-gray-600">
                    Google広告とGoogleアナリティクスのデータを取得するには、
                    Googleアカウントとの連携が必要です。
                </p>

                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <h4 class="font-semibold mb-2">必要な権限</h4>
                    <ul class="space-y-1 text-sm text-gray-600">
                        <li>• Google Ads API へのアクセス</li>
                        <li>• Google Analytics 読み取り権限</li>
                        <li>• 基本的なプロフィール情報</li>
                    </ul>
                </div>

                <flux:button wire:click="connect" icon="link">
                    Googleアカウントと連携
                </flux:button>
            </div>
        @endif

        @if (session('message'))
            <flux:alert variant="success" class="mt-4">
                {{ session('message') }}
            </flux:alert>
        @endif
    </div>
</flux:card>
```

### 3. レポート生成フォーム

**`resources/views/livewire/reports/generate-report.blade.php`**

```blade
<?php

use App\Models\AdAccount;
use App\Models\AnalyticsProperty;
use App\Jobs\GenerateAnalysisReport;
use App\Enums\ReportType;
use Livewire\Volt\Component;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;

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

        $this->adAccounts = AdAccount::where('user_id', $user->id)
            ->where('is_active', true)
            ->get();

        $this->analyticsProperties = AnalyticsProperty::where('user_id', $user->id)
            ->where('is_active', true)
            ->get();

        // デフォルト日付（先週）
        $this->startDate = now()->subWeek()->startOfWeek()->format('Y-m-d');
        $this->endDate = now()->subWeek()->endOfWeek()->format('Y-m-d');

        // デフォルトアカウント
        if ($this->adAccounts->isNotEmpty()) {
            $this->adAccountId = $this->adAccounts->first()->id;
        }
    }

    public function updatedReportType($value): void
    {
        // レポートタイプに応じて期間を自動設定
        match($value) {
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
            // ジョブをキューに追加
            GenerateAnalysisReport::dispatch(
                Auth::id(),
                $this->adAccountId,
                $this->analyticsPropertyId,
                $this->startDate,
                $this->endDate,
                $this->reportType
            );

            session()->flash('message', 'レポート生成を開始しました。完了次第、通知いたします。');

            // レポート一覧にリダイレクト
            $this->redirect('/reports', navigate: true);

        } catch (\Exception $e) {
            session()->flash('error', 'レポート生成の開始に失敗しました: ' . $e->getMessage());
            $this->isGenerating = false;
        }
    }
}; ?>

<div>
    <flux:heading size="lg">レポート生成</flux:heading>

    <form wire:submit="generate" class="mt-6 space-y-6">
        {{-- レポートタイプ --}}
        <flux:field>
            <flux:label>レポートタイプ</flux:label>
            <flux:select wire:model.live="reportType">
                <option value="daily">日次レポート</option>
                <option value="weekly">週次レポート</option>
                <option value="monthly">月次レポート</option>
                <option value="custom">カスタム期間</option>
            </flux:select>
            <flux:error name="reportType" />
        </flux:field>

        {{-- 広告アカウント --}}
        <flux:field>
            <flux:label>広告アカウント</flux:label>
            <flux:select wire:model="adAccountId">
                <option value="">選択してください</option>
                @foreach($adAccounts as $account)
                    <option value="{{ $account->id }}">
                        {{ $account->account_name }}
                    </option>
                @endforeach
            </flux:select>
            <flux:error name="adAccountId" />
        </flux:field>

        {{-- Analyticsプロパティ（オプション）--}}
        <flux:field>
            <flux:label>Analyticsプロパティ（オプション）</flux:label>
            <flux:select wire:model="analyticsPropertyId">
                <option value="">選択しない</option>
                @foreach($analyticsProperties as $property)
                    <option value="{{ $property->id }}">
                        {{ $property->property_name }}
                    </option>
                @endforeach
            </flux:select>
            <flux:description>
                Analyticsデータも含めて分析する場合は選択してください
            </flux:description>
        </flux:field>

        {{-- 期間 --}}
        <div class="grid grid-cols-2 gap-4">
            <flux:field>
                <flux:label>開始日</flux:label>
                <flux:input
                    type="date"
                    wire:model="startDate"
                    :disabled="reportType !== 'custom'"
                />
                <flux:error name="startDate" />
            </flux:field>

            <flux:field>
                <flux:label>終了日</flux:label>
                <flux:input
                    type="date"
                    wire:model="endDate"
                    :disabled="reportType !== 'custom'"
                />
                <flux:error name="endDate" />
            </flux:field>
        </div>

        {{-- プレビュー --}}
        @if($startDate && $endDate)
            <flux:card variant="ghost">
                <div class="text-sm text-gray-600">
                    <strong>分析期間:</strong>
                    {{ \Carbon\Carbon::parse($startDate)->isoFormat('YYYY年MM月DD日') }}
                    〜
                    {{ \Carbon\Carbon::parse($endDate)->isoFormat('YYYY年MM月DD日') }}
                    ({{ \Carbon\Carbon::parse($startDate)->diffInDays($endDate) + 1 }}日間)
                </div>
            </flux:card>
        @endif

        {{-- エラーメッセージ --}}
        @if (session('error'))
            <flux:alert variant="danger">
                {{ session('error') }}
            </flux:alert>
        @endif

        {{-- ボタン --}}
        <div class="flex gap-3">
            <flux:button
                type="submit"
                variant="primary"
                :disabled="$isGenerating"
                icon="sparkles"
            >
                <span wire:loading.remove wire:target="generate">
                    レポート生成
                </span>
                <span wire:loading wire:target="generate">
                    生成中...
                </span>
            </flux:button>

            <flux:button
                variant="ghost"
                href="/reports"
                wire:navigate
            >
                キャンセル
            </flux:button>
        </div>
    </form>
</div>
```

### 4. レポート一覧（ページネーション付き）

**`resources/views/livewire/reports/report-list.blade.php`**

```blade
<?php

use App\Models\AnalysisReport;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Livewire\Volt\Component;
use function Livewire\Volt\{with, usesFileUploads};

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
            ->with(['adAccount', 'analyticsProperty']);

        // 検索
        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('adAccount', fn($q) => $q->where('account_name', 'like', "%{$this->search}%"))
                  ->orWhere('report_type', 'like', "%{$this->search}%");
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
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="検索..."
                icon="magnifying-glass"
            />
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
                            $variant = match($report->status) {
                                'completed' => 'success',
                                'processing' => 'info',
                                'failed' => 'danger',
                                default => 'neutral',
                            };
                        @endphp
                        <flux:badge :variant="$variant">
                            {{ $report->status }}
                        </flux:badge>
                    </div>

                    {{-- レポート情報 --}}
                    <div class="flex-1">
                        <h3 class="font-semibold text-lg">
                            {{ $report->adAccount->account_name }}
                        </h3>

                        <div class="text-sm text-gray-600 mt-1">
                            {{ $report->report_type }} |
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
                        @if($report->status === 'completed')
                            <flux:button
                                variant="primary"
                                size="sm"
                                href="/reports/{{ $report->id }}"
                                wire:navigate
                            >
                                詳細
                            </flux:button>
                        @endif

                        @if($report->status === 'failed')
                            <flux:button
                                variant="ghost"
                                size="sm"
                                wire:click="retry({{ $report->id }})"
                            >
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
                    <flux:button
                        href="/reports/generate"
                        wire:navigate
                        class="mt-4"
                    >
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
```

## Volt のベストプラクティス

### 1. 適切なコンポーネント分割

```blade
{{-- 大きなページは複数のコンポーネントに分割 --}}

{{-- dashboard.blade.php --}}
<div>
    <livewire:dashboard.metrics-summary />
    <livewire:dashboard.recent-insights />
    <livewire:dashboard.quick-actions />
</div>
```

### 2. リアクティブプロパティの活用

```blade
<?php
// wire:model.live で即座に反応
state(['searchTerm' => '']);

$filteredResults = computed(function () {
    return Model::where('name', 'like', "%{$this->searchTerm}%")->get();
});
?>

<div>
    <input type="text" wire:model.live.debounce.300ms="searchTerm">

    @foreach($this->filteredResults as $result)
        <div>{{ $result->name }}</div>
    @endforeach
</div>
```

### 3. ローディング状態の表示

```blade
<flux:button wire:click="save">
    <span wire:loading.remove wire:target="save">保存</span>
    <span wire:loading wire:target="save">保存中...</span>
</flux:button>

<div wire:loading wire:target="save" class="overlay">
    処理中...
</div>
```

### 4. 確認ダイアログ

```blade
<flux:button
    wire:click="delete"
    wire:confirm="本当に削除しますか？"
    variant="danger"
>
    削除
</flux:button>
```

### 5. ナビゲーション（SPA 風）

```blade
{{-- wire:navigate でスムーズな画面遷移 --}}
<flux:button href="/reports" wire:navigate>
    レポート一覧
</flux:button>
```

## 次のステップ

1. **基本レイアウトの作成**

    - `resources/views/layouts/app.blade.php`
    - ナビゲーション
    - サイドバー

2. **認証画面（Fortify + Flux）**

    - ログイン
    - 登録
    - パスワードリセット

3. **各機能の Volt コンポーネント実装**
    - 上記の例を参考に実装

詳細な実装を始めましょうか？どの画面から作成しますか？
