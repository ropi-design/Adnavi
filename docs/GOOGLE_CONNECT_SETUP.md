# Google アカウント連携のセットアップ

Google 広告と Analytics のデータを取得するために、Google アカウントとの連携が必要です。

## セットアップ手順

### 1. Google Cloud Console でプロジェクト作成

1. https://console.cloud.google.com/ にアクセス
2. 新しいプロジェクトを作成
3. 「Adnavi」という名前を推奨

### 2. OAuth 同意画面の設定

1. 「APIs & Services」→「OAuth consent screen」を選択
2. 外部ユーザータイプを選択
3. 必要な情報を入力：
    - アプリ名: Adnavi
    - ユーザーサポートメール: あなたのメールアドレス
    - 開発者連絡先: あなたのメールアドレス
4. 「スコープの追加」をクリック
5. 以下のスコープを追加：
    - `https://www.googleapis.com/auth/userinfo.email`
    - `https://www.googleapis.com/auth/userinfo.profile`
    - `https://www.googleapis.com/auth/adwords`（広告用）
    - `https://www.googleapis.com/auth/analytics.readonly`（Analytics 用）
6. テストユーザーとしてあなた自身の Gmail アドレスを追加

### 3. 認証情報の作成（OAuth 2.0 Client ID）

1. 「APIs & Services」→「Credentials」を選択
2. 「+ CREATE CREDENTIALS」→「OAuth 2.0 Client ID」を選択
3. アプリケーションの種類: 「Web application」
4. 名前: 「Adnavi Web Client」
5. 承認済みのリダイレクト URI に以下を追加：
    ```
    http://localhost:8000/auth/google/callback
    ```
6. 「作成」をクリック
7. Client ID と Client Secret をコピー

### 4. 必要な API を有効化

1. 「APIs & Services」→「Library」を選択
2. 以下の API を有効化：
    - Google Ads API
    - Google Analytics Data API
    - Google Analytics Admin API

### 5. .env ファイルに設定を追加

プロジェクトの`.env`ファイルに以下を追加：

```env
# Google OAuth
GOOGLE_CLIENT_ID=あなたのClient ID
GOOGLE_CLIENT_SECRET=あなたのClient Secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

# Google Ads API
GOOGLE_ADS_DEVELOPER_TOKEN=your-developer-token
GOOGLE_ADS_LOGIN_CUSTOMER_ID=1234567890
```

### 6. Google Ads API のテストアカウント取得

Google Ads API を使うには、テストアカウントが必要です：

1. https://ads.google.com/aw/getstarted にアクセス
2. テストアカウントを作成（実際の課金なし）
3. Customer ID を取得（例: 123-456-7890）
4. `.env`の`GOOGLE_ADS_LOGIN_CUSTOMER_ID`に設定

### 7. Socialite パッケージのインストール確認

Laravel Socialite がインストールされているか確認：

```bash
composer require laravel/socialite
```

インストールされていない場合は実行してください。

### 8. 設定をクリア

```bash
php artisan config:clear
php artisan route:clear
```

## 使い方

### 連携方法

1. ブラウザで http://localhost:8000 にアクセス
2. ログイン
3. サイドバーから「Google 連携」をクリック
4. 「Google アカウントと連携する」ボタンをクリック
5. Google アカウントでログイン
6. 権限を承認
7. 連携完了

### 連携解除

「Google 連携」画面で「連携解除」ボタンをクリック

## トラブルシューティング

### エラー: "redirect_uri_mismatch"

-   Google Cloud Console のリダイレクト URI が正しく設定されているか確認
-   URL が完全に一致しているか確認（http vs https、末尾のスラッシュなど）

### エラー: "invalid_client"

-   Client ID と Client Secret が正しく`.env`に設定されているか確認
-   キャッシュをクリア：`php artisan config:clear`

### エラー: "access_denied"

-   OAuth 同意画面でテストユーザーとして追加されているか確認
-   承認済みスコープが正しいか確認

### API エラー: "developer_token_not_approved"

-   Google Ads API のテストアカウントを使用しているか確認
-   本番環境では正式な Developer Token が必要

## 本番環境の設定

本番環境では：

1. OAuth 同意画面を本番公開に変更
2. 適切なリダイレクト URI を設定（本番ドメイン）
3. Google Ads API の正式な Developer Token を取得
4. セキュリティ設定を強化

## 参考リンク

-   [Google Cloud Console](https://console.cloud.google.com/)
-   [Google Ads API ドキュメント](https://developers.google.com/google-ads/api/docs/start)
-   [Google Analytics API ドキュメント](https://developers.google.com/analytics/devguides/reporting)
-   [Laravel Socialite](https://laravel.com/docs/socialite)
