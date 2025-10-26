# Adnavi - AI 駆動型広告分析プラットフォーム アーキテクチャ設計

## 1. システム概要

Google 広告と Google アナリティクスのデータを統合し、Gemini AI による自動分析と改善提案を行うプラットフォーム

### 主要機能

-   Google Ads API 連携（キャンペーン、広告グループ、キーワード、コンバージョンデータ取得）
-   Google Analytics API 連携（トラフィック、行動、コンバージョンデータ取得）
-   Gemini AI による自動効果分析
-   改善点の自動抽出
-   具体的施策の提案生成
-   レポートダッシュボード

## 2. 技術スタック

### バックエンド

-   Laravel 12.x
-   PHP 8.3+
-   SQLite/MySQL（本番環境では切り替え可能）

### フロントエンド

-   Livewire 3.x（リアクティブ UI）
-   Livewire Flux（UI コンポーネント）
-   Volt（シングルファイルコンポーネント）
-   Tailwind CSS

### 外部 API

-   Google Ads API
-   Google Analytics Data API (GA4)
-   Google Gemini API (Vertex AI or AI Studio)

### 認証

-   Laravel Fortify（ユーザー認証）
-   Google OAuth 2.0（Google API 認証）

### ジョブ/キュー

-   Laravel Queue（データ取得の非同期処理）
-   Laravel Scheduler（定期実行）

## 3. データベース設計

### テーブル構成

#### 3.1 google_accounts

ユーザーの Google 認証情報

```
- id
- user_id (FK)
- google_id
- email
- access_token (encrypted)
- refresh_token (encrypted)
- token_expires_at
- created_at, updated_at
```

#### 3.2 ad_accounts

Google 広告アカウント

```
- id
- user_id (FK)
- google_account_id (FK)
- customer_id
- account_name
- currency
- timezone
- is_active
- last_synced_at
- created_at, updated_at
```

#### 3.3 analytics_properties

Google Analytics プロパティ

```
- id
- user_id (FK)
- google_account_id (FK)
- property_id
- property_name
- timezone
- is_active
- last_synced_at
- created_at, updated_at
```

#### 3.4 campaigns

キャンペーンデータ

```
- id
- ad_account_id (FK)
- campaign_id (Google ID)
- campaign_name
- campaign_type
- status
- budget_amount
- budget_type
- created_at, updated_at
```

#### 3.5 ad_metrics_daily

日次広告パフォーマンスデータ

```
- id
- campaign_id (FK)
- date
- impressions
- clicks
- cost
- conversions
- conversion_value
- ctr
- cpc
- cpa
- roas
- created_at, updated_at
- UNIQUE(campaign_id, date)
```

#### 3.6 analytics_metrics_daily

日次アナリティクスデータ

```
- id
- analytics_property_id (FK)
- date
- sessions
- users
- new_users
- pageviews
- bounce_rate
- avg_session_duration
- conversions
- conversion_rate
- created_at, updated_at
- UNIQUE(analytics_property_id, date)
```

#### 3.7 analysis_reports

AI 分析レポート

```
- id
- user_id (FK)
- ad_account_id (FK)
- analytics_property_id (FK nullable)
- report_type (daily, weekly, monthly, custom)
- start_date
- end_date
- status (pending, processing, completed, failed)
- raw_data (JSON - 分析に使用した生データ)
- analysis_result (JSON - Geminiの分析結果)
- created_at, updated_at
```

#### 3.8 insights

抽出された洞察

```
- id
- analysis_report_id (FK)
- category (performance, budget, targeting, creative, conversion)
- priority (high, medium, low)
- title
- description
- impact_score (1-10)
- confidence_score (0-1)
- created_at, updated_at
```

#### 3.9 recommendations

具体的な改善施策

```
- id
- insight_id (FK)
- title
- description
- action_type (budget_adjustment, keyword_addition, ad_copy_change, etc.)
- estimated_impact
- implementation_difficulty (easy, medium, hard)
- specific_actions (JSON - 具体的な実施手順)
- status (pending, in_progress, implemented, dismissed)
- implemented_at
- created_at, updated_at
```

## 4. ディレクトリ構造

```
app/
├── Models/
│   ├── User.php
│   ├── GoogleAccount.php
│   ├── AdAccount.php
│   ├── AnalyticsProperty.php
│   ├── Campaign.php
│   ├── AdMetricsDaily.php
│   ├── AnalyticsMetricsDaily.php
│   ├── AnalysisReport.php
│   ├── Insight.php
│   └── Recommendation.php
│
├── Services/
│   ├── Google/
│   │   ├── GoogleAuthService.php          # OAuth認証
│   │   ├── GoogleAdsService.php           # Ads API連携
│   │   └── GoogleAnalyticsService.php     # Analytics API連携
│   ├── AI/
│   │   ├── GeminiService.php              # Gemini API連携
│   │   └── PromptBuilder.php              # プロンプト生成
│   └── Analysis/
│       ├── DataAggregator.php             # データ集約
│       ├── PerformanceAnalyzer.php        # パフォーマンス分析
│       └── RecommendationGenerator.php    # 施策生成
│
├── Jobs/
│   ├── SyncGoogleAdsData.php              # 広告データ同期
│   ├── SyncGoogleAnalyticsData.php        # アナリティクスデータ同期
│   ├── GenerateAnalysisReport.php         # 分析レポート生成
│   └── ProcessRecommendations.php         # 施策処理
│
├── Http/
│   └── Controllers/
│       └── GoogleAuthController.php       # OAuth callback
│
├── Livewire/
│   ├── Dashboard/
│   │   ├── Overview.php                   # ダッシュボード概要
│   │   └── MetricsChart.php              # メトリクスチャート
│   ├── Accounts/
│   │   ├── ConnectGoogle.php             # Google接続
│   │   ├── AdAccountList.php             # 広告アカウント一覧
│   │   └── AnalyticsPropertyList.php     # Analytics一覧
│   ├── Reports/
│   │   ├── ReportList.php                # レポート一覧
│   │   ├── ReportDetail.php              # レポート詳細
│   │   └── GenerateReport.php            # レポート生成
│   ├── Insights/
│   │   ├── InsightList.php               # 洞察一覧
│   │   └── InsightDetail.php             # 洞察詳細
│   └── Recommendations/
│       ├── RecommendationList.php        # 施策一覧
│       ├── RecommendationDetail.php      # 施策詳細
│       └── ImplementationTracker.php     # 実施追跡
│
├── DataTransferObjects/
│   ├── AdMetricsData.php
│   ├── AnalyticsData.php
│   └── AnalysisData.php
│
└── Enums/
    ├── ReportType.php
    ├── InsightCategory.php
    ├── Priority.php
    └── RecommendationStatus.php

resources/
├── views/
│   ├── livewire/
│   │   ├── dashboard/...
│   │   ├── accounts/...
│   │   ├── reports/...
│   │   ├── insights/...
│   │   └── recommendations/...
│   └── layouts/
│       └── app.blade.php
│
└── css/
    └── app.css

database/
├── migrations/
│   ├── xxxx_create_google_accounts_table.php
│   ├── xxxx_create_ad_accounts_table.php
│   ├── xxxx_create_analytics_properties_table.php
│   ├── xxxx_create_campaigns_table.php
│   ├── xxxx_create_ad_metrics_daily_table.php
│   ├── xxxx_create_analytics_metrics_daily_table.php
│   ├── xxxx_create_analysis_reports_table.php
│   ├── xxxx_create_insights_table.php
│   └── xxxx_create_recommendations_table.php
│
└── seeders/
    └── DemoDataSeeder.php

config/
├── google-ads.php      # Google Ads設定
├── google-analytics.php # Google Analytics設定
└── gemini.php          # Gemini設定
```

## 5. 主要なワークフロー

### 5.1 初期セットアップフロー

```
1. ユーザー登録/ログイン (Fortify)
2. Googleアカウント連携 (OAuth 2.0)
   - Ads API権限取得
   - Analytics API権限取得
3. 広告アカウント選択
4. Analyticsプロパティ選択
5. 初回データ同期
```

### 5.2 日次データ同期フロー

```
1. Scheduler実行（毎日深夜）
2. SyncGoogleAdsData Job起動
   - 前日のデータ取得
   - ad_metrics_dailyに保存
3. SyncGoogleAnalyticsData Job起動
   - 前日のデータ取得
   - analytics_metrics_daily に保存
4. データ取得完了通知
```

### 5.3 分析レポート生成フロー

```
1. ユーザーがレポート生成をリクエスト
   - 期間指定（週次、月次、カスタム）
2. GenerateAnalysisReport Job起動
3. DataAggregator でデータ集約
   - 広告データとアナリティクスデータを結合
   - 期間でフィルタリング
4. GeminiService でAI分析
   - プロンプト生成
   - Gemini APIコール
   - 分析結果パース
5. Insight抽出
   - カテゴリ分類
   - 優先度設定
   - インパクトスコア算出
6. Recommendation生成
   - 具体的アクションプラン
   - 実施難易度評価
   - 期待効果推定
7. レポート完成通知
```

### 5.4 施策実施追跡フロー

```
1. ユーザーが施策を確認
2. 施策を「実施中」にマーク
3. 実施後、「実施済み」にマーク
4. 実施前後のメトリクス比較
5. 効果検証レポート生成
```

## 6. Gemini 統合設計

### 6.1 プロンプト設計

#### 分析プロンプトテンプレート

```
あなたはデジタルマーケティングの専門家です。
以下のGoogle広告とGoogleアナリティクスのデータを分析し、
パフォーマンスの評価と改善提案を行ってください。

## データ期間
{start_date} 〜 {end_date}

## Google広告データ
- 総インプレッション: {impressions}
- 総クリック数: {clicks}
- CTR: {ctr}%
- 総コスト: {cost}円
- 総コンバージョン数: {conversions}
- CPA: {cpa}円
- ROAS: {roas}

### キャンペーン別パフォーマンス
{campaign_data}

## Googleアナリティクスデータ
- セッション数: {sessions}
- ユーザー数: {users}
- 直帰率: {bounce_rate}%
- 平均セッション時間: {avg_duration}秒
- コンバージョン率: {conversion_rate}%

## 分析タスク
1. 全体的なパフォーマンス評価（5段階）
2. 特に注目すべき指標の特定
3. パフォーマンスが良いキャンペーン/悪いキャンペーンの分析
4. 改善機会の特定（優先度順）
5. 具体的な改善施策の提案（実施手順付き）

## 出力形式
JSON形式で以下の構造で出力してください：
{json_structure}
```

### 6.2 分析結果構造

```json
{
  "overall_performance": {
    "score": 4,
    "summary": "全体的に良好なパフォーマンス...",
    "key_metrics": [...]
  },
  "insights": [
    {
      "category": "performance",
      "priority": "high",
      "title": "キャンペーンAのCPAが高い",
      "description": "...",
      "impact_score": 8,
      "confidence_score": 0.9
    }
  ],
  "recommendations": [
    {
      "insight_id": 0,
      "title": "キャンペーンAの予算を再配分",
      "description": "...",
      "action_type": "budget_adjustment",
      "estimated_impact": "+20% ROI",
      "difficulty": "easy",
      "specific_actions": [
        "キャンペーンAの日予算を5000円に削減",
        "キャンペーンBの日予算を8000円に増額"
      ]
    }
  ]
}
```

## 7. セキュリティ考慮事項

-   Google OAuth トークンの暗号化保存
-   API キーの環境変数管理
-   CSRF 保護（Livewire 標準）
-   XSS 対策（Blade エスケープ）
-   Rate Limiting（API 呼び出し制限）
-   ユーザーデータの分離（Row Level Security）

## 8. パフォーマンス最適化

-   データ取得のバッチ処理
-   キューワーカーの並列実行
-   メトリクスデータのインデックス
-   レポート結果のキャッシング
-   ページネーション
-   Lazy Loading

## 9. 開発フェーズ

### Phase 1: 基礎構築（Week 1-2）

-   [ ] データベース設計・マイグレーション
-   [ ] 基本モデル作成
-   [ ] 認証システム整備

### Phase 2: Google API 連携（Week 3-4）

-   [ ] Google OAuth 実装
-   [ ] Google Ads API 連携
-   [ ] Google Analytics API 連携
-   [ ] データ同期ジョブ実装

### Phase 3: Gemini 統合（Week 5-6）

-   [ ] Gemini API 連携
-   [ ] プロンプトエンジニアリング
-   [ ] 分析ロジック実装
-   [ ] レポート生成

### Phase 4: UI/UX 構築（Week 7-8）

-   [ ] ダッシュボード画面
-   [ ] レポート表示画面
-   [ ] インサイト・施策管理画面
-   [ ] データビジュアライゼーション

### Phase 5: 最適化・テスト（Week 9-10）

-   [ ] パフォーマンステスト
-   [ ] セキュリティ監査
-   [ ] ユーザーテスト
-   [ ] バグ修正

## 10. 環境変数設定例

```env
# Google OAuth
GOOGLE_CLIENT_ID=xxx
GOOGLE_CLIENT_SECRET=xxx
GOOGLE_REDIRECT_URI=http://localhost/auth/google/callback

# Google Ads API
GOOGLE_ADS_DEVELOPER_TOKEN=xxx
GOOGLE_ADS_LOGIN_CUSTOMER_ID=xxx

# Google Analytics
GOOGLE_ANALYTICS_PROPERTY_ID=xxx

# Gemini API
GEMINI_API_KEY=xxx
GEMINI_MODEL=gemini-1.5-pro
GEMINI_PROJECT_ID=xxx (Vertex AIの場合)

# Queue
QUEUE_CONNECTION=database
```

## 11. 次のステップ

1. 必要な Composer パッケージのインストール
2. データベースマイグレーション作成
3. Google API 認証情報の取得
4. Gemini API 認証情報の取得
5. 基本的なモデルとサービスクラスの実装開始
