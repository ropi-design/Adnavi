# 🔗 Google OAuth 連携の完全セットアップガイド

現在、テストデータの `test@example.com` が表示されていますが、実際の Google 連携を動作させる手順です。

## ステップ 1: テストデータの削除

```bash
./clear_test_google_account.sh
```

## ステップ 2: Google Cloud Console でプロジェクト作成

### 1. プロジェクト作成

1. https://console.cloud.google.com/ にアクセス
2. 左上のプロジェクト選択 → 「新しいプロジェクト」をクリック
3. プロジェクト名: `Adnavi` （任意）
4. 「作成」をクリック

### 2. OAuth 同意画面の設定

1. 左メニュー「APIs & Services」→「OAuth consent screen」を選択
2. **User Type**: 「External（外部）」を選択 → 「作成」
3. **アプリ情報**:
    - アプリ名: `Adnavi`
    - ユーザーサポートメール: あなたの Gmail アドレス
    - アプリのロゴ: （スキップ可）
4. **デベロッパーの連絡先情報**: あなたの Gmail アドレス
5. 「保存して次へ」をクリック

### 3. スコープの追加

1. 「スコープを追加または削除」をクリック
2. 以下のスコープを検索して追加：
    - `https://www.googleapis.com/auth/userinfo.email`
    - `https://www.googleapis.com/auth/userinfo.profile`
    - `https://www.googleapis.com/auth/adwords`
    - `https://www.googleapis.com/auth/analytics.readonly`
3. 「更新」→「保存して次へ」をクリック

### 4. テストユーザーの追加

1. 「ADD USERS」をクリック
2. あなた自身の Gmail アドレスを追加
3. 「保存して次へ」をクリック
4. 「ダッシュボードに戻る」をクリック

### 5. 認証情報の作成

1. 左メニュー「Credentials（認証情報）」をクリック
2. 上部の「+ CREATE CREDENTIALS」→「OAuth client ID」を選択
3. **Application type（アプリケーションの種類）**: 「Web application」
4. **名前**: `Adnavi Web Client`
5. **承認済みのリダイレクト URI**:
    ```
    http://localhost/auth/google/callback
    ```
    ※ ポート番号を確認してください（8000 の場合は `http://localhost:8000/auth/google/callback`）
6. 「作成」をクリック
7. **Client ID** と **Client Secret** が表示されます → コピーしておく

### 6. 必要な API を有効化

1. 左メニュー「Library（ライブラリ）」をクリック
2. 以下を検索して有効化：
    - **Google Ads API** → 「有効にする」
    - **Google Analytics Data API** → 「有効にする」
    - **Google Analytics Admin API** → 「有効にする」

## ステップ 3: Laravel の .env ファイルに設定追加

プロジェクトのルートにある `.env` ファイルを開いて、以下を追加：

```env
# Google OAuth
GOOGLE_CLIENT_ID=あなたのClient ID
GOOGLE_CLIENT_SECRET=あなたのClient Secret
GOOGLE_REDIRECT_URI=http://localhost/auth/google/callback
```

※ `GOOGLE_CLIENT_ID` と `GOOGLE_CLIENT_SECRET` には、先ほどコピーした値を貼り付けてください

## ステップ 4: キャッシュのクリア

```bash
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan route:clear
./vendor/bin/sail artisan cache:clear
```

## ステップ 5: 動作確認

1. ブラウザで http://localhost/accounts/google にアクセス
2. 「Google アカウントと連携する」ボタンをクリック
3. Google 認証画面が表示されます
4. テストユーザーとして追加した Gmail アドレスでログイン
5. 権限の承認画面で「許可」をクリック
6. アプリに戻り、「✓ 連携済み」と表示されたら成功！

## ⚠️ トラブルシューティング

### エラー: "redirect_uri_mismatch"

**原因**: リダイレクト URI が一致していない

**解決法**:

1. Google Cloud Console → 認証情報 → OAuth 2.0 クライアント ID を開く
2. 承認済みのリダイレクト URI に以下が**完全一致**で登録されているか確認：
    - `http://localhost/auth/google/callback` または
    - `http://localhost:8000/auth/google/callback`
3. URL の末尾のスラッシュ、http/https に注意

### エラー: "invalid_client"

**原因**: Client ID または Client Secret が間違っている

**解決法**:

1. `.env` ファイルの `GOOGLE_CLIENT_ID` と `GOOGLE_CLIENT_SECRET` を確認
2. Google Cloud Console の認証情報から再度コピー
3. キャッシュをクリア: `./vendor/bin/sail artisan config:clear`

### エラー: "access_denied"

**原因**: テストユーザーとして登録されていない

**解決法**:

1. Google Cloud Console → OAuth consent screen → Test users
2. 使用している Gmail アドレスが登録されているか確認
3. 登録されていない場合は「ADD USERS」で追加

### エラー: "This app is blocked"

**原因**: スコープの検証が必要、または未承認のスコープ

**解決法**:

1. Google Cloud Console → OAuth consent screen
2. Publishing status が「Testing」になっているか確認
3. スコープが正しく追加されているか確認
4. テストモードでは最大 100 ユーザーまで利用可能

## 📝 次のステップ

連携が完了したら：

1. **広告アカウント**の登録: http://localhost/accounts/ads
2. **Analytics プロパティ**の登録: http://localhost/accounts/analytics
3. データ同期の開始

## 🔐 本番環境への移行

本番環境で使用する場合：

1. OAuth 同意画面を「本番公開」に変更（Google の審査が必要）
2. 本番ドメインのリダイレクト URI を追加
3. HTTPS の使用を推奨
4. 環境変数の適切な管理

## 参考リンク

-   [Google Cloud Console](https://console.cloud.google.com/)
-   [Google OAuth 2.0 ドキュメント](https://developers.google.com/identity/protocols/oauth2)
-   [Laravel Socialite ドキュメント](https://laravel.com/docs/socialite)
