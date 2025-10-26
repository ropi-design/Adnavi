# Laravel 12 対応と日本語化設定

## Laravel 12 の主な特徴

このプロジェクトは Laravel 12.x を想定して設計されています。

### システム要件

-   **PHP**: 8.3 以上
-   **Composer**: 2.x
-   **Node.js**: 20.x 以上（推奨）
-   **MySQL**: 8.0 以上 または PostgreSQL: 13 以上（本番環境）
-   **SQLite**: 3.35 以上（開発環境）

### Laravel 12 の新機能活用

#### 1. ネイティブ Enum サポート

PHP 8.3+ のネイティブ Enum を活用します。

```php
// app/Enums/ReportType.php
<?php

namespace App\Enums;

enum ReportType: string
{
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
    case CUSTOM = 'custom';

    public function label(): string
    {
        return match($this) {
            self::DAILY => '日次レポート',
            self::WEEKLY => '週次レポート',
            self::MONTHLY => '月次レポート',
            self::CUSTOM => 'カスタムレポート',
        };
    }
}
```

#### 2. 型付きプロパティの活用

```php
// app/Models/AdAccount.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdAccount extends Model
{
    protected $fillable = [
        'user_id',
        'google_account_id',
        'customer_id',
        'account_name',
    ];

    // Laravel 12の強化された型システム
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_synced_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
```

#### 3. より洗練されたバリデーション

```php
use Illuminate\Validation\Rules\Password;

// フォームリクエスト内
public function rules(): array
{
    return [
        'email' => ['required', 'email', 'unique:users'],
        'password' => [
            'required',
            'confirmed',
            Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()
        ],
        'budget_amount' => ['required', 'numeric', 'min:1000'],
        'start_date' => ['required', 'date', 'after_or_equal:today'],
        'end_date' => ['required', 'date', 'after:start_date'],
    ];
}
```

## 日本語化設定の詳細

### 実装済み言語ファイル

以下の言語ファイルが実装されています：

1. **`lang/ja/validation.php`**

    - 全バリデーションルールの日本語メッセージ
    - アプリ固有の属性名（キャンペーン、広告アカウントなど）
    - カスタムバリデーションメッセージ

2. **`lang/ja/auth.php`**

    - 認証関連のメッセージ
    - ログイン失敗、パスワード不一致など

3. **`lang/ja/passwords.php`**

    - パスワードリセット関連のメッセージ

4. **`lang/ja/pagination.php`**
    - ページネーション表示の日本語化

### 設定手順

#### 1. Composer パッケージのインストール

```bash
composer require --dev laravel-lang/common laravel-lang/lang laravel-lang/publisher
```

#### 2. 言語ファイルの発行

```bash
# Laravel 12の言語ファイル発行コマンド
php artisan lang:add ja
```

既存の言語ファイルは既にプロジェクトに含まれているため、このステップはオプションです。

#### 3. 環境変数の設定

`.env` ファイルに追加：

```env
APP_LOCALE=ja
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=ja_JP
APP_TIMEZONE=Asia/Tokyo
```

#### 4. AppServiceProvider の設定

`app/Providers/AppServiceProvider.php` は既に設定済みです：

```php
use Carbon\Carbon;

public function boot(): void
{
    // 日本語ロケール設定
    Carbon::setLocale(config('app.locale'));

    // 日付フォーマットのカスタマイズ
    if (config('app.locale') === 'ja') {
        setlocale(LC_TIME, 'ja_JP.UTF-8');
    }
}
```

### 使用例

#### Livewire コンポーネントでのバリデーション

```php
<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Validate;

new class extends Component
{
    #[Validate('required|min:3|max:255')]
    public string $campaign_name = '';

    #[Validate('required|numeric|min:1000')]
    public int $budget_amount = 0;

    #[Validate('required|date|after_or_equal:today')]
    public string $start_date = '';

    public function save(): void
    {
        $this->validate();

        // 保存処理
        // バリデーションエラーは自動的に日本語で表示されます
    }
}
```

#### フォームリクエストでのカスタムメッセージ

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCampaignRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'campaign_name' => ['required', 'max:255'],
            'budget_amount' => ['required', 'numeric', 'min:1000'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ];
    }

    // カスタムメッセージは lang/ja/validation.php で定義済み
    // 必要に応じてここでオーバーライド可能
    public function messages(): array
    {
        return [
            'budget_amount.min' => '予算は:min円以上で設定してください。',
        ];
    }

    // 属性名も lang/ja/validation.php で定義済み
    public function attributes(): array
    {
        return [
            'campaign_name' => 'キャンペーン名',
            'budget_amount' => '予算金額',
            'start_date' => '開始日',
            'end_date' => '終了日',
        ];
    }
}
```

### ブレードテンプレートでの日付表示

```blade
{{-- Carbonの日本語フォーマット --}}
<p>作成日: {{ $campaign->created_at->isoFormat('YYYY年MM月DD日 HH:mm') }}</p>

{{-- 相対時間（〜前）--}}
<p>更新: {{ $campaign->updated_at->diffForHumans() }}</p>

{{-- 曜日を含む表示 --}}
<p>開始: {{ $campaign->start_date->isoFormat('YYYY年MM月DD日 (ddd)') }}</p>
```

### 数値のフォーマット

```php
// app/Helpers/NumberHelper.php
<?php

namespace App\Helpers;

class NumberHelper
{
    /**
     * 金額を日本円形式でフォーマット
     */
    public static function formatCurrency(float $amount): string
    {
        return '¥' . number_format($amount, 0);
    }

    /**
     * パーセンテージをフォーマット
     */
    public static function formatPercentage(float $value, int $decimals = 2): string
    {
        return number_format($value, $decimals) . '%';
    }

    /**
     * 大きな数値を読みやすくフォーマット（万、億）
     */
    public static function formatLargeNumber(int $number): string
    {
        if ($number >= 100000000) {
            return number_format($number / 100000000, 1) . '億';
        } elseif ($number >= 10000) {
            return number_format($number / 10000, 1) . '万';
        }

        return number_format($number);
    }
}
```

### Livewire Volt での使用例

```blade
<?php
use App\Models\Campaign;
use App\Helpers\NumberHelper;

$campaigns = Campaign::with('adMetrics')->latest()->get();
?>

<div>
    <h2>キャンペーン一覧</h2>

    @foreach($campaigns as $campaign)
        <div class="campaign-card">
            <h3>{{ $campaign->campaign_name }}</h3>

            <div class="metrics">
                <div>
                    <span>予算:</span>
                    <strong>{{ NumberHelper::formatCurrency($campaign->budget_amount) }}</strong>
                </div>

                <div>
                    <span>インプレッション:</span>
                    <strong>{{ NumberHelper::formatLargeNumber($campaign->impressions) }}</strong>
                </div>

                <div>
                    <span>CTR:</span>
                    <strong>{{ NumberHelper::formatPercentage($campaign->ctr) }}</strong>
                </div>
            </div>

            <p class="text-sm text-gray-500">
                {{ $campaign->created_at->diffForHumans() }}
            </p>
        </div>
    @endforeach
</div>
```

## テスト時の日本語対応

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class CampaignTest extends TestCase
{
    public function test_validation_messages_are_in_japanese(): void
    {
        $response = $this->post('/campaigns', [
            'campaign_name' => '',
            'budget_amount' => 500,
        ]);

        $response->assertSessionHasErrors([
            'campaign_name' => 'キャンペーン名は、必ず指定してください。',
            'budget_amount' => '予算金額には、1000以上の数字を指定してください。',
        ]);
    }
}
```

## 多言語対応への拡張

将来的に英語などの他言語にも対応する場合：

```php
// config/app.php
'locale' => env('APP_LOCALE', 'ja'),
'available_locales' => ['ja', 'en'],

// ミドルウェアで言語切り替え
Route::middleware(['web', 'locale'])->group(function () {
    // routes
});
```

## トラブルシューティング

### Carbon の日本語が表示されない

```bash
# システムロケールの確認
locale -a | grep ja

# 必要に応じてロケールをインストール（Ubuntu/Debian）
sudo locale-gen ja_JP.UTF-8
sudo update-locale
```

### バリデーションメッセージが英語のまま

```bash
# キャッシュをクリア
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# .env の APP_LOCALE を確認
cat .env | grep APP_LOCALE
```

## まとめ

-   ✅ Laravel 12.x 対応（PHP 8.3+）
-   ✅ 完全な日本語バリデーションメッセージ
-   ✅ 日付・時刻の日本語フォーマット
-   ✅ 日本円・数値の適切なフォーマット
-   ✅ アプリ固有の用語（キャンペーン、広告アカウントなど）の日本語化
-   ✅ 認証・パスワードリセットの日本語メッセージ

これで、ユーザーに優しい日本語対応の Laravel アプリケーションが構築できます！
