# Gemini 分析機能のセットアップガイド

## 1. マイグレーション実行

```bash
php artisan migrate
```

これで以下のテーブルが更新されます：

-   `analysis_reports`: `overall_score`と`summary`フィールド追加
-   `recommendations`: `analysis_report_id`フィールド追加
-   `insights`: `status`フィールド追加

## 2. Gemini API キーの設定

### 手順 1: API キーを取得

https://makersuite.google.com/app/apikey にアクセスして、Google アカウントでログイン

### 手順 2: API キーを作成

1. 「Create API Key」をクリック
2. プロジェクトを選択または新規作成
3. 生成された API キーをコピー

### 手順 3: .env ファイルに設定

```env
GEMINI_API_KEY=your-api-key-here
GEMINI_MODEL=gemini-1.5-pro-latest
```

### 手順 4: キャッシュをクリア

```bash
php artisan config:clear
```

## 3. キューを起動

```bash
php artisan queue:work
```

## 動作確認

1. ブラウザで http://localhost:8000 にアクセス
2. ログインまたは新規登録
3. 「レポート生成」ページに移動
4. レポートタイプを選択（例: 週次）
5. 「AI レポート生成」ボタンをクリック

## 確認方法

レポートが正常に生成されたか確認：

```bash
# レポート一覧を確認
php artisan tinker
>>> App\Models\AnalysisReport::latest()->first()

# インサイトを確認
>>> App\Models\Insight::latest()->get()

# 改善施策を確認
>>> App\Models\Recommendation::latest()->get()
```

## トラブルシューティング

### API キーエラー

-   `.env`ファイルに正しい API キーが設定されているか確認
-   `php artisan config:clear` を実行

### ジョブが実行されない

-   `php artisan queue:work` を起動しているか確認
-   `storage/logs/laravel.log` でエラーログを確認

### データがないエラー

-   Google Ads アカウントとデータの同期が必要
-   Google 連携ページでアカウントを接続

## 次のステップ

1. ダミーデータでテスト
2. 実際の Google Ads データと同期
3. Gemini 分析結果を確認
4. インサイトと改善施策をレビュー
