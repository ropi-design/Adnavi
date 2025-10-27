# Gemini API 連携ガイド

Adnavi で Gemini による AI 分析機能を有効にする方法です。

## 1. Google AI Studio で API キーを取得

1. https://makersuite.google.com/app/apikey にアクセス
2. Google アカウントでログイン
3. 「Create API Key」をクリック
4. プロジェクトを選択または新規作成
5. 生成された API キーをコピー

## 2. .env ファイルに設定を追加

```env
# Gemini API 設定
GEMINI_API_KEY=your-api-key-here
GEMINI_MODEL=gemini-1.5-pro-latest
```

### モデルの選択

-   `gemini-1.5-pro-latest`: 高精度な分析（推奨）
-   `gemini-1.5-flash-latest`: 高速・低コスト

## 3. キュー設定の確認

Gemini 分析は非同期ジョブで実行されます。以下を確認してください：

```env
QUEUE_CONNECTION=database
```

## 4. キューを起動

```bash
# 開発環境
php artisan queue:work

# 本番環境（Supervisorなどで常時起動推奨）
php artisan queue:work --daemon
```

## 5. 動作確認

1. ダッシュボードでレポート生成をリクエスト
2. ジョブが正しく処理されることを確認
3. インサイトと改善施策が生成されることを確認

## トラブルシューティング

### API キーエラー

```bash
# .envファイルが正しく読み込まれているか確認
php artisan config:clear
php artisan config:cache
```

### ジョブが実行されない

```bash
# ジューが起動しているか確認
php artisan queue:work --tries=3

# ログを確認
tail -f storage/logs/laravel.log
```

### Gemini API のレート制限

-   無料プラン: 60 リクエスト/分、32,000 トークン/分
-   有料プラン: より高い制限

## 分析プロセスの流れ

1. **データ集約**: DataAggregator が広告・Analytics データを集約
2. **プロンプト生成**: Gemini 用の分析プロンプトを生成
3. **AI 分析**: Gemini API でパフォーマンス分析を実行
4. **結果保存**:
    - インサイト: 発見された問題や機会
    - 改善施策: 具体的なアクションプラン
    - 評価スコア: パフォーマンスの総合評価

## カスタマイズ

### プロンプトテンプレートの編集

`app/Services/AI/GeminiService.php` の `buildAnalysisPrompt()` メソッドを編集して、分析内容をカスタマイズできます。

### 分析パラメータの調整

`config/gemini.php` で以下のパラメータを調整できます：

-   `temperature`: 創造性（0-1、デフォルト: 0.7）
-   `top_p`: 多様性（0-1、デフォルト: 0.95）
-   `top_k`: トークン選択（デフォルト: 40）
-   `max_output_tokens`: 最大出力長（デフォルト: 8192）
