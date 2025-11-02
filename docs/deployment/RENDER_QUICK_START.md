# Render デプロイクイックスタート

## 📋 事前準備チェックリスト

### ✅ コードの準備

-   [x] GitHub リポジトリにコードをプッシュ済み
-   [x] `render.yaml` がリポジトリに含まれている
-   [x] `feature`ブランチが最新

### 🔑 環境変数の準備

以下の値を準備してください：

1. **APP_KEY** (必須)

    ```bash
    php artisan key:generate --show
    ```

    出力された値をコピーしてください

2. **GEMINI_API_KEY**

    - Google AI Studio から取得
    - https://aistudio.google.com/app/apikey

3. **GOOGLE_CLIENT_ID** と **GOOGLE_CLIENT_SECRET**

    - Google Cloud Console から取得
    - OAuth 2.0 認証情報を作成

4. **GOOGLE_REDIRECT_URI**
    - `https://your-app-name.onrender.com/auth/google/callback`
    - デプロイ後に確定した URL に置き換え

## 🚀 デプロイ手順

### ステップ 1: Render Dashboard にアクセス

1. https://dashboard.render.com/ にログイン
2. **New +** ボタンをクリック
3. **Blueprint** を選択

### ステップ 2: GitHub リポジトリを接続

1. **Connect account** をクリック
2. GitHub アカウントを認証
3. リポジトリ `ropi-design/Adnavi` を選択
4. **Branch**: `feature` を選択
5. **Apply**

### ステップ 3: サービスが作成されるのを待つ

Blueprint により以下のサービスが自動作成されます：

-   ✅ **adnavi** (Web サービス)
-   ✅ **adnavi-db** (PostgreSQL データベース)
-   ✅ **adnavi-queue** (Queue Worker)

### ステップ 4: 環境変数を設定

各サービス（Web サービスと Worker サービス）に以下の環境変数を追加：

#### Web サービス (`adnavi`) の環境変数

1. Web サービスを選択
2. **Environment** タブをクリック
3. 以下の環境変数を追加：

```
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
GEMINI_API_KEY=your-gemini-api-key
GEMINI_MODEL=gemini-2.5-flash
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=https://your-app-name.onrender.com/auth/google/callback
APP_URL=https://your-app-name.onrender.com
```

**注意**: `APP_URL` と `GOOGLE_REDIRECT_URI` は、デプロイ完了後に表示される実際の URL に置き換えてください。

#### Worker サービス (`adnavi-queue`) の環境変数

Worker サービスにも同じ環境変数を設定してください（`APP_URL` と `GOOGLE_REDIRECT_URI` を除く）。

### ステップ 5: 初回デプロイの確認

1. **Events** タブでビルドとデプロイの進行状況を確認
2. **Logs** タブでエラーがないか確認
3. デプロイが完了したら、**URL** (例: `https://adnavi.onrender.com`) にアクセス

### ステップ 6: ストレージリンクの作成

初回デプロイ後、Render のシェルから実行：

1. Web サービスを選択
2. **Shell** タブをクリック
3. 以下のコマンドを実行：

```bash
php artisan storage:link
```

## 🐛 トラブルシューティング

### デプロイが失敗する場合

**Build エラー:**

-   **Logs** タブで詳細なエラーメッセージを確認
-   `composer install` エラー: PHP 拡張機能が不足している可能性
-   `npm run build` エラー: Node.js バージョンの問題

**Runtime エラー:**

-   `APP_KEY` が設定されているか確認
-   データベース接続情報が正しく設定されているか確認
-   環境変数が正しく設定されているか確認

### データベース接続エラー

-   `DB_CONNECTION=pgsql` が設定されているか確認
-   データベースサービスが起動しているか確認
-   データベースサービスの**Internal Database URL**を確認

### アプリケーションが起動しない

-   **Logs** タブでエラーメッセージを確認
-   `php artisan config:clear` をシェルから実行
-   環境変数が正しく設定されているか再確認

## 📝 追加設定（オプション）

### カスタムドメインの設定

1. Web サービスを選択
2. **Settings** → **Custom Domains**
3. ドメインを追加
4. DNS レコードを設定

### 自動デプロイの設定

デフォルトで、GitHub へのプッシュ時に自動デプロイされます。

### 本番環境へのマージ

`feature` ブランチを `main` ブランチにマージしてから、Render で `main` ブランチを使用するように設定することもできます。

## ✅ デプロイ完了チェックリスト

-   [ ] Web サービスが正常に起動している
-   [ ] データベース接続が正常
-   [ ] ログイン機能が動作している
-   [ ] Google OAuth 連携が動作している
-   [ ] Gemini AI 機能が動作している
-   [ ] 静的アセット（CSS/JS）が正しく読み込まれている
-   [ ] ストレージリンクが作成されている

## 📚 参考ドキュメント

詳細な手順は `RENDER_DEPLOYMENT.md` を参照してください。
