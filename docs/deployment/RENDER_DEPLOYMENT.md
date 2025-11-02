# Render へのデプロイガイド

このドキュメントは、Adnavi アプリケーションを Render にデプロイする手順を説明します。

## 前提条件

-   Render アカウントを作成済み
-   GitHub リポジトリにコードをプッシュ済み
-   必要な環境変数を準備済み

## デプロイ手順

### 1. GitHub にコードをプッシュ

```bash
# 変更をコミット
git add .
git commit -m "改善施策の具体的案表示とパスワードページレイアウト修正、Renderデプロイ設定追加"

# メインブランチにプッシュ（またはfeatureブランチからマージ）
git push origin main
```

### 2. Render でサービスを作成

1. [Render Dashboard](https://dashboard.render.com/)にログイン
2. **New +** ボタンをクリック
3. **Blueprint** を選択（render.yaml を使用する場合）
4. GitHub リポジトリを接続
5. リポジトリを選択して**Apply**

または、個別にサービスを作成する場合：

#### Web サービス

1. **New +** → **Web Service**
2. GitHub リポジトリを接続
3. 以下の設定を入力：
    - **Name**: `adnavi`
    - **Environment**: `PHP`
    - **Build Command**:
        ```bash
        composer install --no-dev --optimize-autoloader && npm ci && npm run build && php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan view:cache
        ```
    - **Start Command**: `php artisan serve --host=0.0.0.0 --port=$PORT`

#### PostgreSQL データベース

1. **New +** → **PostgreSQL**
2. 以下の設定を入力：
    - **Name**: `adnavi-db`
    - **Database**: `adnavi`
    - **User**: `adnavi`

#### Worker サービス（オプション）

1. **New +** → **Background Worker**
2. 以下の設定を入力：
    - **Name**: `adnavi-queue`
    - **Environment**: `PHP`
    - **Build Command**: `composer install --no-dev --optimize-autoloader && php artisan config:cache`
    - **Start Command**: `php artisan queue:work --sleep=3 --tries=3 --max-time=3600`

### 3. 環境変数の設定

Render Dashboard で各サービスに以下の環境変数を設定します：

#### Web サービスと Worker サービス共通

| キー                   | 値                                                   | 説明                                                     |
| ---------------------- | ---------------------------------------------------- | -------------------------------------------------------- |
| `APP_ENV`              | `production`                                         | アプリケーション環境                                     |
| `APP_DEBUG`            | `false`                                              | デバッグモード（本番は false）                           |
| `APP_URL`              | `https://your-app.onrender.com`                      | アプリケーション URL（Render が自動設定）                |
| `LOG_CHANNEL`          | `stderr`                                             | ログ出力先                                               |
| `LOG_LEVEL`            | `error`                                              | ログレベル                                               |
| `DB_CONNECTION`        | `pgsql`                                              | データベース接続                                         |
| `GEMINI_API_KEY`       | `your-api-key`                                       | Gemini API キー                                          |
| `GEMINI_MODEL`         | `gemini-2.5-flash`                                   | 使用する Gemini モデル                                   |
| `GOOGLE_CLIENT_ID`     | `your-client-id`                                     | Google OAuth Client ID                                   |
| `GOOGLE_CLIENT_SECRET` | `your-client-secret`                                 | Google OAuth Client Secret                               |
| `GOOGLE_REDIRECT_URI`  | `https://your-app.onrender.com/auth/google/callback` | Google OAuth リダイレクト URI                            |
| `QUEUE_CONNECTION`     | `database`                                           | キュー接続                                               |
| `APP_KEY`              | `base64:...`                                         | アプリケーションキー（`php artisan key:generate`で生成） |

#### データベース接続情報

データベースサービスの環境変数は自動的に設定されます：

-   `DB_HOST`
-   `DB_PORT`
-   `DB_DATABASE`
-   `DB_USERNAME`
-   `DB_PASSWORD`

**重要**: `APP_KEY`は必ず設定してください。設定しないとセッションや暗号化が正しく動作しません。

```bash
# ローカルで生成
php artisan key:generate --show
# 出力されたキーを環境変数に設定
```

### 4. 初回デプロイ後の設定

#### ストレージリンクの作成

Render のシェルから以下のコマンドを実行：

```bash
php artisan storage:link
```

または、buildCommand に追加：

```bash
php artisan storage:link &&
```

#### シーディング（オプション）

必要なデータがある場合：

```bash
php artisan db:seed
```

### 5. カスタムドメインの設定（オプション）

1. Render Dashboard で Web サービスを選択
2. **Settings** → **Custom Domains**
3. ドメインを追加
4. DNS レコードを設定（Render が指示を表示）

### 6. 定期的なメンテナンス

#### ログの確認

```bash
# Render Dashboardの Logs タブから確認
```

#### マイグレーションの実行

新しいマイグレーションがある場合、Render Dashboard のシェルから実行：

```bash
php artisan migrate --force
```

#### キャッシュのクリア

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

## トラブルシューティング

### デプロイが失敗する場合

1. **Build Logs**を確認

    - Composer の依存関係エラー
    - npm ビルドエラー
    - 環境変数の不足

2. **Runtime Logs**を確認
    - アプリケーション起動エラー
    - データベース接続エラー

### データベース接続エラー

-   `DB_CONNECTION`が`pgsql`に設定されているか確認
-   データベースサービスの環境変数が正しく設定されているか確認
-   データベースサービスが起動しているか確認

### 静的アセットが表示されない

-   `npm run build`が正常に実行されたか確認
-   `php artisan storage:link`を実行したか確認
-   `public/build`ディレクトリが存在するか確認

### セッションエラー

-   `APP_KEY`が正しく設定されているか確認
-   セッションドライバーを`database`または`redis`に変更

## 注意事項

-   Render の Free プランでは、アプリケーションが 15 分間非アクティブになるとスリープします
-   初回リクエスト時は起動に時間がかかることがあります
-   ストレージは永続化されません（ファイルは再デプロイ時に失われます）
-   大きなファイルは外部ストレージ（S3 等）の使用を推奨

## 次のステップ

-   SSL 証明書の設定（自動で HTTPS 化されます）
-   パフォーマンスモニタリングの設定
-   エラートラッキングの設定（Sentry 等）
-   CI/CD パイプラインの設定
