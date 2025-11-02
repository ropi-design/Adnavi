# 環境変数設定テンプレート

`.env` ファイルに以下の設定を追加してください。

## 基本設定

```env
APP_NAME=Adnavi
APP_ENV=local
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=true
APP_TIMEZONE=Asia/Tokyo
APP_URL=http://localhost:8000

# 日本語化設定
APP_LOCALE=ja
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=ja_JP
```

## データベース設定

### 開発環境（SQLite）

```env
DB_CONNECTION=sqlite
# DB_DATABASE=/absolute/path/to/database.sqlite
```

### 本番環境（MySQL）

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=adnavi
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

## キュー・キャッシュ設定

```env
# キュー設定
QUEUE_CONNECTION=database

# キャッシュ設定
CACHE_STORE=database
# 本番環境ではRedisを推奨
# CACHE_STORE=redis
```

## Google OAuth 2.0 認証

```env
GOOGLE_CLIENT_ID=your-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
```

## Google Ads API

```env
GOOGLE_ADS_DEVELOPER_TOKEN=your-developer-token
GOOGLE_ADS_LOGIN_CUSTOMER_ID=1234567890
```

## Google Analytics API

```env
GOOGLE_ANALYTICS_DEFAULT_PROPERTY_ID=properties/123456789
```

## Gemini API 設定

### オプション A: Google AI Studio（推奨・簡単）

1. [Google AI Studio](https://makersuite.google.com/app/apikey) で API キーを取得
2. 以下の設定を追加：

```env
GEMINI_DRIVER=api
GEMINI_API_KEY=your-gemini-api-key-here
GEMINI_MODEL=gemini-2.5-flash
REQUEST_TIMEOUT_MS=30000
```

### オプション B: Vertex AI（エンタープライズ向け）

```env
GEMINI_DRIVER=vertex-ai
GOOGLE_CLOUD_PROJECT_ID=your-project-id
GOOGLE_APPLICATION_CREDENTIALS=/path/to/service-account-key.json
VERTEX_AI_LOCATION=us-central1
VERTEX_AI_MODEL=gemini-1.5-pro
```

### API キーの取得方法

1. https://makersuite.google.com/app/apikey にアクセス
2. Google アカウントでログイン
3. 「Create API Key」をクリック
4. プロジェクトを選択または作成
5. 生成された API キーをコピーして `.env` に設定

## メール設定

### 開発環境（ログ出力）

```env
MAIL_MAILER=log
```

### 本番環境（SMTP）

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@adnavi.com"
MAIL_FROM_NAME="${APP_NAME}"
```

## セッション設定

```env
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
```

## ロギング設定

```env
LOG_CHANNEL=stack
LOG_LEVEL=debug
# 本番環境では info または error を推奨
```

## その他

```env
FILESYSTEM_DISK=local
BROADCAST_CONNECTION=log
```

## 設定の確認

環境変数を設定した後、以下のコマンドで確認できます：

```bash
# アプリケーションキーの生成（まだの場合）
php artisan key:generate

# 設定のキャッシュクリア
php artisan config:clear

# 設定の確認
php artisan config:show app
php artisan config:show database

# キャッシュの再生成（本番環境のみ）
php artisan config:cache
```

## セキュリティ注意事項

-   `.env` ファイルは絶対に Git にコミットしないでください
-   本番環境では `APP_DEBUG=false` に設定してください
-   本番環境では強力なパスワードを使用してください
-   API キーは定期的にローテーションしてください
