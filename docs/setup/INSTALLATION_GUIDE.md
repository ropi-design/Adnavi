# Adnavi - インストール＆セットアップガイド

## 1. 必要な Composer パッケージ

```bash
# Google API クライアントライブラリ
composer require google/apiclient

# Google Ads API
composer require googleads/google-ads-php

# Google Analytics Data API (GA4)
composer require google/analytics-data

# Gemini / Vertex AI (選択肢)
# オプション1: Google AI PHP SDK (推奨 - シンプル)
composer require google/generative-ai-php

# オプション2: Vertex AI経由の場合
composer require google/cloud-aiplatform

# データ変換用
composer require spatie/laravel-data

# Enum管理
# (Laravel 12はPHP 8.3+なのでネイティブEnum使用可能)

# 日本語言語ファイル
composer require --dev laravel-lang/common laravel-lang/lang laravel-lang/publisher

# チャート表示用（オプション）
composer require asantibanez/livewire-charts

# HTTP Client拡張（必要に応じて）
composer require guzzlehttp/guzzle
```

## 2. Google Cloud Platform セットアップ

### 2.1 プロジェクト作成

1. [Google Cloud Console](https://console.cloud.google.com/)にアクセス
2. 新しいプロジェクト作成
3. プロジェクト ID をメモ

### 2.2 API の有効化

以下の API を有効化：

-   Google Ads API
-   Google Analytics Data API (GA4)
-   Google OAuth2 API
-   Vertex AI API または Generative Language API (Gemini 用)

### 2.3 OAuth 2.0 認証情報の作成

1. 「認証情報」→「認証情報を作成」→「OAuth クライアント ID」
2. アプリケーションの種類：ウェブアプリケーション
3. 承認済みのリダイレクト URI：
    - `http://localhost:8000/auth/google/callback` (開発環境)
    - `https://yourdomain.com/auth/google/callback` (本番環境)
4. クライアント ID とクライアントシークレットを取得

### 2.4 Google Ads API アクセス

1. [Google Ads API Center](https://developers.google.com/google-ads/api/docs/start)
2. Developer Token を申請・取得
3. テスト環境用の管理者アカウントを設定

### 2.5 Gemini API アクセス

#### オプション A: Google AI Studio（簡単・推奨）

1. [Google AI Studio](https://makersuite.google.com/app/apikey)にアクセス
2. API キーを生成
3. 無料枠あり、クレジットカード登録不要

#### オプション B: Vertex AI（本番推奨・高度な機能）

1. Google Cloud Console で Vertex AI API を有効化
2. サービスアカウント作成
3. JSON キーファイルをダウンロード
4. より細かい権限管理とエンタープライズ機能

## 3. 環境変数設定

`.env` ファイルに以下を追加：

```env
# ===================================
# Google OAuth 2.0
# ===================================
GOOGLE_CLIENT_ID=your-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"

# ===================================
# Google Ads API
# ===================================
GOOGLE_ADS_DEVELOPER_TOKEN=your-developer-token
# 管理者アカウントのCustomer ID（ハイフンなし）
GOOGLE_ADS_LOGIN_CUSTOMER_ID=1234567890

# ===================================
# Google Analytics API
# ===================================
# デフォルトのプロパティID（ユーザーごとに設定可能）
GOOGLE_ANALYTICS_DEFAULT_PROPERTY_ID=properties/123456789

# ===================================
# Gemini API (オプションAの場合)
# ===================================
GEMINI_API_KEY=your-gemini-api-key
GEMINI_MODEL=gemini-1.5-pro-latest
# または gemini-1.5-flash-latest (高速・低コスト)

# ===================================
# Vertex AI (オプションBの場合)
# ===================================
GOOGLE_CLOUD_PROJECT_ID=your-project-id
GOOGLE_APPLICATION_CREDENTIALS=/path/to/service-account-key.json
VERTEX_AI_LOCATION=us-central1
VERTEX_AI_MODEL=gemini-1.5-pro

# ===================================
# Queue設定
# ===================================
QUEUE_CONNECTION=database
# 本番環境ではRedisやSQSを推奨

# ===================================
# キャッシュ設定
# ===================================
CACHE_DRIVER=redis
# 開発環境ではfileでもOK
```

## 4. 日本語化設定

### 4.1 言語ファイルのインストール

```bash
# Laravel 12の言語ファイルをインストール
php artisan lang:add ja

# または手動で言語ファイルを発行
php artisan lang:publish ja
```

### 4.2 アプリケーションのロケール設定

`.env` ファイルに追加：

```env
APP_LOCALE=ja
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=ja_JP
```

または `config/app.php` を直接編集：

```php
'locale' => 'ja',
'fallback_locale' => 'en',
'faker_locale' => 'ja_JP',
```

### 4.3 タイムゾーン設定

`.env` ファイル：

```env
APP_TIMEZONE=Asia/Tokyo
```

または `config/app.php`：

```php
'timezone' => 'Asia/Tokyo',
```

### 4.4 カスタムバリデーションメッセージ

`lang/ja/validation.php` のカスタマイズ例：

```php
<?php

return [
    // カスタム属性名
    'attributes' => [
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'name' => '名前',
        'campaign_name' => 'キャンペーン名',
        'budget_amount' => '予算金額',
        'start_date' => '開始日',
        'end_date' => '終了日',
        'ad_account_id' => '広告アカウント',
        'analytics_property_id' => 'Analyticsプロパティ',
    ],

    // カスタムメッセージ
    'custom' => [
        'email' => [
            'required' => 'メールアドレスは必須です。',
            'email' => '有効なメールアドレスを入力してください。',
        ],
        'budget_amount' => [
            'min' => '予算金額は:min円以上で設定してください。',
        ],
    ],
];
```

### 4.5 日付のローカライズ

`app/Providers/AppServiceProvider.php` に追加：

```php
use Carbon\Carbon;

public function boot(): void
{
    // Carbonの日本語化
    Carbon::setLocale('ja');

    // 日付フォーマットのカスタマイズ
    setlocale(LC_TIME, 'ja_JP.UTF-8');
}
```

## 5. 設定ファイル作成

### config/google-ads.php

```bash
php artisan vendor:publish --provider="GoogleAds\GoogleAds\GoogleAdsServiceProvider"
```

手動作成する場合：

```php
<?php

return [
    'developer_token' => env('GOOGLE_ADS_DEVELOPER_TOKEN'),
    'login_customer_id' => env('GOOGLE_ADS_LOGIN_CUSTOMER_ID'),
    'oauth2' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    ],
    'api_version' => 'v16', // 最新バージョンを確認
];
```

### config/google-analytics.php

```php
<?php

return [
    'property_id' => env('GOOGLE_ANALYTICS_DEFAULT_PROPERTY_ID'),
    'credentials' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    ],
];
```

### config/gemini.php

```php
<?php

return [
    // API接続方式: 'ai-studio' or 'vertex-ai'
    'driver' => env('GEMINI_DRIVER', 'ai-studio'),

    // AI Studio設定
    'ai_studio' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-1.5-pro-latest'),
    ],

    // Vertex AI設定
    'vertex_ai' => [
        'project_id' => env('GOOGLE_CLOUD_PROJECT_ID'),
        'location' => env('VERTEX_AI_LOCATION', 'us-central1'),
        'model' => env('VERTEX_AI_MODEL', 'gemini-1.5-pro'),
        'credentials' => env('GOOGLE_APPLICATION_CREDENTIALS'),
    ],

    // 生成パラメータ
    'generation_config' => [
        'temperature' => 0.7,
        'top_p' => 0.95,
        'top_k' => 40,
        'max_output_tokens' => 8192,
    ],

    // レート制限
    'rate_limit' => [
        'requests_per_minute' => 60,
        'tokens_per_minute' => 32000,
    ],
];
```

## 6. データベースセットアップ

```bash
# マイグレーション実行
php artisan migrate

# 開発用データ投入（オプション）
php artisan db:seed --class=DemoDataSeeder
```

## 7. キュー設定

### データベースキューテーブル作成

```bash
php artisan queue:table
php artisan migrate
```

### キューワーカー起動

```bash
# 開発環境
php artisan queue:work

# 本番環境（Supervisor推奨）
# /etc/supervisor/conf.d/adnavi-worker.conf
[program:adnavi-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopascompromise=3600
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/worker.log
```

## 8. スケジューラー設定

Crontab に追加：

```bash
* * * * * cd /path/to/adnavi && php artisan schedule:run >> /dev/null 2>&1
```

`app/Console/Kernel.php` でスケジュール定義：

```php
protected function schedule(Schedule $schedule)
{
    // 毎日深夜2時にGoogle Adsデータ同期
    $schedule->job(new SyncGoogleAdsData)->dailyAt('02:00');

    // 毎日深夜3時にGoogle Analyticsデータ同期
    $schedule->job(new SyncGoogleAnalyticsData)->dailyAt('03:00');

    // 毎週月曜朝8時に週次レポート自動生成
    $schedule->job(new GenerateWeeklyReports)->weeklyOn(1, '08:00');
}
```

## 9. 権限・スコープ設定

Google OAuth で必要なスコープ：

```php
// app/Services/Google/GoogleAuthService.php
protected $scopes = [
    'https://www.googleapis.com/auth/userinfo.email',
    'https://www.googleapis.com/auth/userinfo.profile',
    'https://www.googleapis.com/auth/adwords', // Google Ads
    'https://www.googleapis.com/auth/analytics.readonly', // Analytics
];
```

## 10. 開発環境起動

```bash
# アプリケーション起動
php artisan serve

# Vite開発サーバー起動（別ターミナル）
npm run dev

# キューワーカー起動（別ターミナル）
php artisan queue:work

# アクセス
# http://localhost:8000
```

## 11. トラブルシューティング

### Google API 認証エラー

-   リダイレクト URI が正確に一致しているか確認
-   API が有効化されているか確認
-   トークンの有効期限を確認（1 時間）

### Google Ads API エラー

-   Developer Token が承認されているか
-   管理者アカウントへのアクセス権があるか
-   Customer ID が正しいか（ハイフンなし）

### Gemini API エラー

-   API キーが正しいか
-   レート制限に達していないか確認
-   モデル名が正確か（gemini-1.5-pro など）

### キューが動作しない

-   `queue:work` が起動しているか
-   データベース接続は正常か
-   failed_jobs テーブルを確認

## 12. テスト実行

```bash
# 全テスト実行
php artisan test

# 特定のテスト
php artisan test --filter=GoogleAuthTest

# カバレッジレポート
php artisan test --coverage
```

## 13. 本番環境デプロイ前チェックリスト

-   [ ] 全ての環境変数が設定されている
-   [ ] `APP_ENV=production`
-   [ ] `APP_DEBUG=false`
-   [ ] データベース接続が正しい
-   [ ] キャッシュ設定（Redis 推奨）
-   [ ] キューワーカーが Supervisor で管理されている
-   [ ] Cron ジョブが設定されている
-   [ ] SSL 証明書が設定されている
-   [ ] ログローテーション設定
-   [ ] バックアップ設定
-   [ ] モニタリング設定（Sentry, Bugsnag など）

## 14. コスト見積もり

### Google Cloud Platform

-   Google Ads API: 無料
-   Google Analytics API: 無料（クォータ制限あり）
-   Gemini API (AI Studio): 無料枠あり
    -   gemini-1.5-pro: 50 リクエスト/日（無料）
    -   gemini-1.5-flash: 1500 リクエスト/日（無料）
-   Vertex AI: 従量課金
    -   gemini-1.5-pro: $0.00025/1K chars（入力）、$0.00125/1K chars（出力）

### インフラ

-   サーバー: VPS/クラウド（月額 $5-50）
-   Redis: マネージドサービス（月額 $10-30）
-   ストレージ: S3 など（月額 $1-10）

**推定月額コスト: $20-100**（トラフィック次第）
