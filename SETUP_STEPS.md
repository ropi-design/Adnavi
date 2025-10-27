# Gemini 分析機能のセットアップ手順

## 📝 手順 1: マイグレーション実行

ターミナルで以下を実行：

```bash
php artisan migrate
```

これでデータベースに必要なカラムが追加されます。

---

## 📝 手順 2: Gemini API キーを取得

### 1. Google AI Studio にアクセス

https://makersuite.google.com/app/apikey

### 2. API キーを作成

-   Google アカウントでログイン
-   「Create API Key」をクリック
-   プロジェクトを選択または新規作成
-   生成されたキーをコピー

### 3. .env ファイルに追加

プロジェクトの`.env`ファイルに以下を追加：

```env
GEMINI_API_KEY=ここにコピーしたAPIキーを貼り付け
GEMINI_MODEL=gemini-1.5-pro-latest
```

### 4. キャッシュをクリア

```bash
php artisan config:clear
php artisan cache:clear
```

---

## 📝 手順 3: キューを起動

Gemini 分析は非同期ジョブで実行されます。新しいターミナルウィンドウで以下を実行：

```bash
php artisan queue:work
```

**重要**: このコマンドは実行し続けてください（Ctrl+C で停止）

---

## 🚀 使い方

### 1. レポートを生成

1. ブラウザで http://localhost:8000 にアクセス
2. ログイン
3. サイドバーから「レポート」をクリック
4. 「レポート生成」または「AI レポート生成」をクリック
5. レポートタイプを選択（日次/週次/月次/カスタム）
6. 必要に応じて期間を指定
7. 「AI レポート生成」ボタンをクリック

### 2. 結果を確認

-   レポート一覧画面でレポートのステータスを確認
-   ステータスが「completed」になったら完了
-   レポート詳細画面でインサイトと改善施策を確認

---

## 🔍 確認方法

### コマンドで確認

```bash
# tinkerを起動
php artisan tinker

# 最新のレポートを確認
App\Models\AnalysisReport::latest()->first()

# インサイトを確認
App\Models\Insight::latest()->get()

# 改善施策を確認
App\Models\Recommendation::latest()->get()
```

---

## ❌ トラブルシューティング

### エラー: API key not set

-   `.env`ファイルに API キーが設定されているか確認
-   `php artisan config:clear`を実行

### ジョブが実行されない

-   `php artisan queue:work`が起動しているか確認
-   `storage/logs/laravel.log`でエラーを確認

### データがないエラー

-   Google Ads アカウントとの連携が必要
-   サイドバーの「Google 連携」から接続

---

## 📊 動作の流れ

1. **ユーザーがレポート生成をリクエスト**
   ↓
2. **レポートレコードが作成される**
   ↓
3. **キューにジョブが追加される**
   ↓
4. **GenerateAnalysisReport ジョブが実行**
   ↓
5. **DataAggregator がデータを集約**
   ↓
6. **GeminiService が Gemini API を呼び出し**
   ↓
7. **AI 分析結果がインサイトと改善施策に変換**
   ↓
8. **レポートステータスが「completed」に**

---

以上で Gemini 分析機能が使えるようになります！
