# 🚀 Google 連携 クイックスタート

現在、画面に表示されている `test@example.com` はダミーデータです。
実際の Google 連携を動作させる最短手順をご案内します。

## 現在の状況

-   ✅ OAuth 認証フローの実装済み
-   ✅ Google Ads/Analytics API のスコープ追加済み
-   ⚠️ Google Cloud Console の設定が未完了
-   ⚠️ テストデータ（test@example.com）が残っている

## 🎯 3 ステップで実際の Google 連携を動かす

### ステップ 1: Google Cloud Console でプロジェクト作成（10 分）

詳細は `GOOGLE_OAUTH_SETUP.md` を参照してください。

**簡易版:**

1. https://console.cloud.google.com/ にアクセス
2. 新しいプロジェクト作成
3. OAuth consent screen 設定（External、テストユーザー追加）
4. 認証情報 → OAuth 2.0 Client ID 作成
5. リダイレクト URI: `http://localhost/auth/google/callback` を設定
6. Client ID と Client Secret をコピー

### ステップ 2: 環境変数の設定（1 分）

以下のコマンドを実行して、対話的に設定：

```bash
./setup_google_oauth.sh
```

または、`.env` ファイルに直接追加：

```env
# Google OAuth
GOOGLE_CLIENT_ID=あなたのClient ID
GOOGLE_CLIENT_SECRET=あなたのClient Secret
GOOGLE_REDIRECT_URI=http://localhost/auth/google/callback
```

### ステップ 3: テストデータの削除とキャッシュクリア（1 分）

```bash
# テストデータ削除
./clear_test_google_account.sh

# キャッシュクリア
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan route:clear
```

## ✨ 動作確認

1. ブラウザで http://localhost/accounts/google にアクセス
2. 「Google アカウントと連携する」ボタンが表示される
3. クリックすると Google 認証画面へ
4. 承認すると、実際の Google アカウントと連携完了！

## 📊 連携後の流れ

### 自動的に取得されるデータ

連携完了後、以下のデータにアクセス可能：

1. **Google 広告アカウント**

    - キャンペーン一覧
    - 広告パフォーマンスデータ
    - 予算情報

2. **Google Analytics プロパティ**
    - セッション数
    - ユーザー数
    - コンバージョン率

### 次のステップ

1. **広告アカウント登録**: http://localhost/accounts/ads

    - 連携した Google アカウントで利用可能な広告アカウントを選択

2. **Analytics プロパティ登録**: http://localhost/accounts/analytics

    - 連携した Google アカウントで利用可能なプロパティを選択

3. **データ同期開始**
    - 選択したアカウント/プロパティのデータを自動取得
    - ダッシュボードで確認可能

## 🔍 現在の実装状況

### ✅ 実装済み

-   OAuth 認証フロー（GoogleAuthController）
-   トークン管理（GoogleAccount モデル）
-   Google Ads API クライアント（GoogleAdsService）
-   スコープ設定（広告 + Analytics）

### 🚧 今後実装予定

-   広告アカウント自動取得 UI
-   Analytics プロパティ自動取得 UI
-   データ同期ジョブの完成
-   定期同期のスケジュール

## ⚠️ よくあるエラーと対処法

### "redirect_uri_mismatch"

→ Google Cloud Console のリダイレクト URI を確認
→ `http://localhost/auth/google/callback` が完全一致で登録されているか

### "invalid_client"

→ `.env` の `GOOGLE_CLIENT_ID` と `GOOGLE_CLIENT_SECRET` を確認
→ `./vendor/bin/sail artisan config:clear` でキャッシュクリア

### "access_denied"

→ OAuth consent screen でテストユーザーとして追加されているか確認

### 画面に "test@example.com" が表示される

→ `./clear_test_google_account.sh` でテストデータを削除
→ ブラウザをリロード

## 📖 詳細ドキュメント

-   **完全なセットアップ手順**: `GOOGLE_OAUTH_SETUP.md`
-   **Google 連携の設定**: `docs/GOOGLE_CONNECT_SETUP.md`
-   **アーキテクチャ**: `docs/ARCHITECTURE.md`

## 💡 開発モードでの注意点

1. **テストモード**: 最大 100 ユーザーまで
2. **スコープ**: 現在 4 つのスコープを要求
3. **トークン有効期限**: 約 1 時間（refresh_token で自動更新）
4. **本番公開**: Google の審査が必要（後で対応可）

---

質問や問題があれば、上記のドキュメントを参照するか、
エラーログ（`storage/logs/laravel.log`）を確認してください。
