# Adnavi - 実装ロードマップ

## Phase 1: 基礎構築（Week 1-2）

### Day 1-2: プロジェクト初期設定

#### タスク

1. 必要なパッケージのインストール

```bash
composer require google/apiclient \
    googleads/google-ads-php \
    google/analytics-data \
    google/generative-ai-php \
    spatie/laravel-data \
    asantibanez/livewire-charts
```

2. 設定ファイルの作成

-   `config/google-ads.php`
-   `config/google-analytics.php`
-   `config/gemini.php`

3. 環境変数の設定

-   Google OAuth 認証情報
-   API キー類

4. 日本語化設定

```bash
# 言語ファイルのインストール
php artisan lang:add ja

# Carbonのロケール設定
# app/Providers/AppServiceProvider.php を編集
```

`.env` に追加：

```env
APP_LOCALE=ja
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=ja_JP
APP_TIMEZONE=Asia/Tokyo
```

### Day 3-5: データベース設計・マイグレーション

#### マイグレーション作成順序

```bash
# 1. Google認証関連
php artisan make:migration create_google_accounts_table
php artisan make:migration create_ad_accounts_table
php artisan make:migration create_analytics_properties_table

# 2. データ保存テーブル
php artisan make:migration create_campaigns_table
php artisan make:migration create_ad_metrics_daily_table
php artisan make:migration create_analytics_metrics_daily_table

# 3. 分析・レポート関連
php artisan make:migration create_analysis_reports_table
php artisan make:migration create_insights_table
php artisan make:migration create_recommendations_table
```

#### 重要なインデックス

-   `ad_metrics_daily`: `(campaign_id, date)` 複合 UNIQUE
-   `analytics_metrics_daily`: `(analytics_property_id, date)` 複合 UNIQUE
-   外部キーには自動的にインデックスが作成される

### Day 6-7: 基本モデルとリレーション

```bash
# モデル生成
php artisan make:model GoogleAccount
php artisan make:model AdAccount
php artisan make:model AnalyticsProperty
php artisan make:model Campaign
php artisan make:model AdMetricsDaily
php artisan make:model AnalyticsMetricsDaily
php artisan make:model AnalysisReport
php artisan make:model Insight
php artisan make:model Recommendation
```

#### 実装するリレーション

-   User → GoogleAccount (1:多)
-   GoogleAccount → AdAccount (1:多)
-   GoogleAccount → AnalyticsProperty (1:多)
-   AdAccount → Campaign (1:多)
-   Campaign → AdMetricsDaily (1:多)
-   AnalysisReport → Insight (1:多)
-   Insight → Recommendation (1:多)

### Day 8-10: Enum 定義と DTO 作成

```bash
# Enum作成
php artisan make:enum ReportType
php artisan make:enum InsightCategory
php artisan make:enum Priority
php artisan make:enum RecommendationStatus

# DTO作成（Spatie Data使用）
php artisan make:data AdMetricsData
php artisan make:data AnalyticsData
php artisan make:data AnalysisData
```

---

## Phase 2: Google API 連携（Week 3-4）

### Day 11-13: Google OAuth 実装

#### タスク

1. GoogleAuthService 作成

```bash
php artisan make:service Google/GoogleAuthService
```

実装内容：

-   OAuth 認証フロー
-   トークン取得
-   トークンリフレッシュ
-   トークン暗号化保存

2. コントローラー作成

```bash
php artisan make:controller GoogleAuthController
```

ルート：

-   `GET /auth/google` - 認証開始
-   `GET /auth/google/callback` - コールバック処理
-   `POST /auth/google/disconnect` - 連携解除

3. Livewire コンポーネント

```bash
php artisan make:volt accounts/connect-google
```

### Day 14-17: Google Ads API 連携

#### タスク

1. GoogleAdsService 作成

```bash
php artisan make:service Google/GoogleAdsService
```

実装メソッド：

-   `getAccessibleCustomers()` - アクセス可能なアカウント取得
-   `getCampaigns($customerId)` - キャンペーン一覧取得
-   `getMetrics($customerId, $startDate, $endDate)` - メトリクス取得
-   `getCampaignPerformance($campaignId, $startDate, $endDate)` - キャンペーン別パフォーマンス

2. SyncGoogleAdsData Job 作成

```bash
php artisan make:job SyncGoogleAdsData
```

処理フロー：

-   全アクティブアカウントを取得
-   前日のデータを取得
-   `ad_metrics_daily` に upsert
-   エラーハンドリング

3. Livewire コンポーネント

```bash
php artisan make:volt accounts/ad-account-list
php artisan make:volt accounts/ad-account-setup
```

### Day 18-21: Google Analytics API 連携

#### タスク

1. GoogleAnalyticsService 作成

```bash
php artisan make:service Google/GoogleAnalyticsService
```

実装メソッド：

-   `getProperties()` - プロパティ一覧
-   `runReport($propertyId, $startDate, $endDate, $metrics, $dimensions)` - レポート実行
-   `getDailyMetrics($propertyId, $date)` - 日次メトリクス取得

2. SyncGoogleAnalyticsData Job 作成

```bash
php artisan make:job SyncGoogleAnalyticsData
```

3. Livewire コンポーネント

```bash
php artisan make:volt accounts/analytics-property-list
php artisan make:volt accounts/analytics-property-setup
```

### Day 22-24: データ同期テスト・最適化

-   エラーハンドリングの強化
-   リトライロジック実装
-   レート制限対策
-   ログ記録の実装

---

## Phase 3: Gemini 統合（Week 5-6）

### Day 25-27: Gemini API サービス実装

#### タスク

1. GeminiService 作成

```bash
php artisan make:service AI/GeminiService
```

実装メソッド：

-   `generateContent($prompt, $options = [])` - コンテンツ生成
-   `analyzePerformance($data)` - パフォーマンス分析
-   `generateRecommendations($insights)` - 施策生成
-   `parseJsonResponse($response)` - JSON 解析

設計ポイント：

-   プロンプトテンプレート管理
-   レスポンスパース処理
-   エラーハンドリング
-   レート制限対策

2. PromptBuilder 作成

```bash
php artisan make:service AI/PromptBuilder
```

実装内容：

-   分析プロンプト生成
-   データフォーマット
-   JSON 構造定義
-   コンテキスト最適化

### Day 28-30: 分析ロジック実装

#### タスク

1. DataAggregator 作成

```bash
php artisan make:service Analysis/DataAggregator
```

実装メソッド：

-   `aggregateAdData($accountId, $startDate, $endDate)` - 広告データ集約
-   `aggregateAnalyticsData($propertyId, $startDate, $endDate)` - アナリティクスデータ集約
-   `mergeData($adData, $analyticsData)` - データ結合
-   `calculateKPIs($data)` - KPI 計算

2. PerformanceAnalyzer 作成

```bash
php artisan make:service Analysis/PerformanceAnalyzer
```

実装メソッド：

-   `analyze($data)` - 総合分析
-   `detectAnomalies($metrics)` - 異常検知
-   `identifyTrends($timeSeriesData)` - トレンド分析
-   `benchmarkPerformance($current, $previous)` - パフォーマンス比較

3. RecommendationGenerator 作成

```bash
php artisan make:service Analysis/RecommendationGenerator
```

### Day 31-33: レポート生成ジョブ実装

```bash
php artisan make:job GenerateAnalysisReport
```

処理フロー：

1. レポート設定取得（期間、対象アカウント）
2. DataAggregator でデータ集約
3. GeminiService で AI 分析
4. 分析結果をパース
5. Insight, Recommendation を保存
6. レポートステータスを完了に更新
7. ユーザーに通知

---

## Phase 4: UI/UX 構築（Week 7-8）

### Day 34-36: ダッシュボード実装

#### Livewire Volt コンポーネント作成

Volt は `resources/views/livewire/` に Blade ファイルとして作成されます。

```bash
# Voltコンポーネント作成（Bladeファイルとして生成）
php artisan make:volt dashboard/overview
php artisan make:volt dashboard/metrics-summary
php artisan make:volt dashboard/metrics-chart
php artisan make:volt dashboard/recent-insights
php artisan make:volt dashboard/quick-actions
```

生成されるファイル：

-   `resources/views/livewire/dashboard/overview.blade.php`
-   `resources/views/livewire/dashboard/metrics-summary.blade.php`
-   など

#### 実装内容（Volt SFC 形式）

各コンポーネントは以下の形式で実装：

```blade
<?php
use function Livewire\Volt\{state, computed, mount};

// ロジック部分
state(['data' => null]);

$loadData = function() {
    // データ取得処理
};
?>

<!-- ビュー部分 -->
<div>
    <!-- UI実装 -->
</div>
```

表示内容：

-   主要メトリクスサマリー（今日、今週、今月）
-   パフォーマンス推移グラフ
-   最近の分析レポート
-   重要なインサイト（優先度高）
-   クイックアクション（レポート生成、データ同期）

### Day 37-39: レポート管理画面

```bash
# Voltコンポーネント作成
php artisan make:volt reports/report-list
php artisan make:volt reports/report-detail
php artisan make:volt reports/generate-report
php artisan make:volt reports/report-filter
```

**重要**: Volt コンポーネントの呼び出し方

```blade
{{-- レイアウトから呼び出し --}}
<livewire:reports.report-list />

{{-- またはVolt関数で --}}
@volt('reports.report-list')
```

機能：

-   レポート一覧表示（フィルタ、ソート、検索）
-   レポート詳細表示
-   レポート生成フォーム（期間選択、アカウント選択）
-   レポートエクスポート（PDF、CSV）

### Day 40-42: インサイト・施策管理画面

```bash
php artisan make:volt insights/insight-list
php artisan make:volt insights/insight-detail
php artisan make:volt insights/insight-filter

php artisan make:volt recommendations/recommendation-list
php artisan make:volt recommendations/recommendation-detail
php artisan make:volt recommendations/implementation-tracker
```

機能：

-   インサイト一覧（カテゴリ、優先度フィルター）
-   インサイト詳細（関連施策表示）
-   施策一覧（ステータス管理）
-   施策詳細（実施手順、期待効果）
-   実施追跡（実施前後比較）

### Day 43-45: データビジュアライゼーション

#### チャート実装

-   時系列チャート（メトリクス推移）
-   比較チャート（期間比較、キャンペーン比較）
-   分布チャート（パフォーマンス分布）
-   ゴール達成率

ライブラリ：

-   Livewire Charts
-   Chart.js（カスタマイズが必要な場合）

### Day 46-48: レスポンシブ対応・UX 改善

-   モバイル最適化
-   ローディング状態表示
-   エラーメッセージ改善
-   ツールチップ・ヘルプテキスト
-   アニメーション・トランジション

---

## Phase 5: 最適化・テスト（Week 9-10）

### Day 49-51: パフォーマンス最適化

#### タスク

1. データベースクエリ最適化

-   N+1 問題解決（Eager Loading）
-   クエリキャッシング
-   インデックス追加

2. キャッシング戦略

```php
// メトリクスデータのキャッシュ
Cache::remember("metrics:{$accountId}:{$date}", 3600, function() {
    return $this->getMetrics();
});

// レポート結果のキャッシュ
Cache::remember("report:{$reportId}", 86400, function() {
    return $this->generateReport();
});
```

3. ジョブ最適化

-   バッチ処理の並列化
-   チャンク処理
-   優先度付きキュー

### Day 52-54: セキュリティ強化

#### チェック項目

-   [ ] SQL インジェクション対策（Eloquent ORM 使用）
-   [ ] XSS 対策（Blade エスケープ）
-   [ ] CSRF 保護（Livewire 標準）
-   [ ] 認証・認可（Middleware）
-   [ ] Rate Limiting 実装
-   [ ] API トークン暗号化
-   [ ] 環境変数の適切な管理
-   [ ] ログからの機密情報除外

### Day 55-57: 自動テスト作成

#### テスト種類

1. ユニットテスト

```bash
php artisan make:test Unit/Services/GoogleAdsServiceTest --unit
php artisan make:test Unit/Services/GeminiServiceTest --unit
php artisan make:test Unit/Services/DataAggregatorTest --unit
```

2. 機能テスト

```bash
php artisan make:test Feature/Auth/GoogleAuthTest
php artisan make:test Feature/Reports/GenerateReportTest
php artisan make:test Feature/Insights/InsightManagementTest
```

3. Livewire コンポーネントテスト

```bash
php artisan make:test Feature/Livewire/DashboardTest
php artisan make:test Feature/Livewire/ReportListTest
```

カバレッジ目標：70%以上

### Day 58-60: 統合テスト・バグ修正

-   エンドツーエンドテスト
-   ブラウザテスト（Laravel Dusk）
-   API モックテスト
-   エラーシナリオテスト
-   バグトラッキングと修正

---

## Phase 6: デプロイ準備（Week 11）

### Day 61-63: 本番環境設定

#### タスク

1. サーバー準備

-   VPS/クラウドプロバイダー選択
-   PHP 8.2+, Nginx/Apache
-   MySQL/PostgreSQL
-   Redis

2. デプロイスクリプト

```bash
php artisan make:command Deploy
```

3. CI/CD 設定（GitHub Actions）

```yaml
# .github/workflows/deploy.yml
name: Deploy
on:
    push:
        branches: [main]
jobs:
    deploy:
        runs-on: ubuntu-latest
        steps:
            - uses: actions/checkout@v3
            - name: Setup PHP
            - name: Install dependencies
            - name: Run tests
            - name: Deploy to production
```

### Day 64-65: モニタリング・ログ設定

1. エラートラッキング

-   Sentry 統合
-   Bugsnag（オプション）

2. アプリケーションモニタリング

-   Laravel Telescope（開発環境）
-   New Relic / DataDog（本番環境）

3. ログ管理

-   ログローテーション設定
-   ログ集約（CloudWatch, Stackdriver）

### Day 66-67: ドキュメント整備

-   API ドキュメント
-   ユーザーマニュアル
-   管理者ガイド
-   トラブルシューティングガイド
-   変更履歴（CHANGELOG.md）

### Day 68-70: 本番デプロイ・検証

-   本番環境デプロイ
-   動作確認
-   パフォーマンステスト
-   セキュリティスキャン
-   バックアップ設定確認

---

## 継続的改善（Post-Launch）

### フェーズ 7: 機能拡張

#### 優先度 High

-   [ ] 自動レポート配信（メール、Slack）
-   [ ] カスタムダッシュボード
-   [ ] 複数アカウント一括管理
-   [ ] 施策の効果測定自動化

#### 優先度 Medium

-   [ ] 予算最適化アルゴリズム
-   [ ] A/B テスト提案機能
-   [ ] 競合分析機能
-   [ ] チームコラボレーション機能

#### 優先度 Low

-   [ ] モバイルアプリ
-   [ ] API 公開
-   [ ] プラグインシステム
-   [ ] 多言語対応

### フェーズ 8: AI 機能強化

-   [ ] カスタムプロンプトテンプレート
-   [ ] 学習型推奨システム
-   [ ] 自然言語クエリ対応
-   [ ] 予測分析機能
-   [ ] 自動 A/B テスト生成

---

## 開発 Tips

### デバッグコマンド

```bash
# Gemini API テスト
php artisan tinker
> app(App\Services\AI\GeminiService::class)->generateContent('テスト');

# ジョブ手動実行
php artisan queue:work --once

# データ同期テスト
php artisan sync:google-ads {ad_account_id} --date=2024-01-01

# レポート生成テスト
php artisan generate:report {user_id} --period=weekly
```

### 便利な Artisan コマンド

```bash
# サービスクラス生成（カスタムコマンド）
php artisan make:service ServiceName

# DTO生成
php artisan make:data DtoName

# Enum生成
php artisan make:enum EnumName
```

### コード品質チェック

```bash
# Laravel Pint（コードスタイル）
./vendor/bin/pint

# PHPStan（静的解析）
./vendor/bin/phpstan analyse

# Pest（テスト）
./vendor/bin/pest --parallel
```

---

## マイルストーン

| Week | フェーズ        | 成果物                         |
| ---- | --------------- | ------------------------------ |
| 1-2  | 基礎構築        | DB 設計、基本モデル            |
| 3-4  | Google API 連携 | データ同期機能                 |
| 5-6  | Gemini 統合     | AI 分析機能                    |
| 7-8  | UI/UX 構築      | ダッシュボード、レポート画面   |
| 9-10 | 最適化・テスト  | テスト完了、パフォーマンス改善 |
| 11   | デプロイ        | 本番環境稼働                   |

---

## 次のアクション

実装を開始する準備が整ったら：

```bash
# Phase 1開始
php artisan migrate:fresh
# マイグレーションファイルを作成していく
```

どのフェーズから始めますか？
