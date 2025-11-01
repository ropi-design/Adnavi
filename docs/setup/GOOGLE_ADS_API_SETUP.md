# Google Ads API 連携セットアップガイド

## 概要

実際の Google Ads アカウントと連携してデータを取得するための完全セットアップガイドです。

## 前提条件

1. Google OAuth 連携が完了していること
2. Google Cloud Console でプロジェクトが作成済みであること
3. Google Ads API が有効化されていること

## ステップ 1: Google Ads API Developer Token の申請

### 1.1 Google Ads アカウントの準備

1. [Google Ads](https://ads.google.com/) にアクセス
2. Google Ads アカウントを作成（まだの場合）
3. テストモードで無料で作成できます

### 1.2 Developer Token の申請

1. Google Ads 管理画面にログイン
2. **ツールと設定** → **API センター** → **設定** に移動
3. **Developer Token** セクションで「今すぐ申請」をクリック
4. 申請フォームに記入：
    - **会社名**: あなたの会社名
    - **統合の種類**: API を使用する
    - **API の使用目的**: データ分析・レポート自動化
    - **統合の説明**: Adnavi での広告分析プラットフォーム開発
5. 申請を送信
6. **審査には 1-2 営業日かかります**

※ テストアカウントでは即座に Developer Token が発行されます

### 1.3 Customer ID の取得

1. Google Ads 管理画面の右上に表示されている**Customer ID**をコピー
2. 形式は `123-456-7890` のようにハイフン区切りです
3. ハイフンを除いた数字（例: `1234567890`）を保存

## ステップ 2: Google Cloud Console での設定

### 2.1 Google Ads API の有効化確認

1. [Google Cloud Console](https://console.cloud.google.com/) にアクセス
2. プロジェクトを選択
3. **API とサービス** → **ライブラリ**
4. 「Google Ads API」を検索して有効化されているか確認

### 2.2 OAuth スコープの確認

OAuth 同意画面で以下のスコープが追加されているか確認：

```
https://www.googleapis.com/auth/userinfo.email
https://www.googleapis.com/auth/userinfo.profile
https://www.googleapis.com/auth/adwords
https://www.googleapis.com/auth/analytics.readonly
```

## ステップ 3: 環境変数の設定

`.env` ファイルに以下を追加・更新：

```env
# Google OAuth (既に設定済みのはず)
GOOGLE_CLIENT_ID=your-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"

# Google Ads API (追加)
GOOGLE_ADS_DEVELOPER_TOKEN=your-developer-token-here
GOOGLE_ADS_LOGIN_CUSTOMER_ID=1234567890
GOOGLE_ADS_API_VERSION=v16
```

**重要**: `GOOGLE_ADS_LOGIN_CUSTOMER_ID` はハイフンなしの数字のみ（例: `1234567890`）

## ステップ 4: Google Ads アカウントとの連携

### 4.1 OAuth 認証の実行

1. `/accounts/google` にアクセス
2. 「Google アカウントと連携する」ボタンをクリック
3. Google アカウントでログイン
4. 権限承認画面で「許可」をクリック
5. **重要**: `https://www.googleapis.com/auth/adwords` スコープを承認する

### 4.2 広告アカウントの登録

連携後、システムが自動的に広告アカウントを検出します（実装済みの場合）。

手動で登録する場合：

1. `/accounts/ads` にアクセス
2. 「新規登録」ボタンをクリック
3. Customer ID を入力
4. アカウント名を入力
5. 保存

## ステップ 5: キャッシュのクリアと動作確認

```bash
# キャッシュをクリア
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# サーバーを再起動
php artisan serve
```

## ステップ 6: データ同期のテスト

### 6.1 同期ジョブの実行

```bash
# Tinkerでテスト
php artisan tinker

>>> use App\Models\GoogleAccount;
>>> use App\Services\Google\GoogleAdsService;
>>> $account = GoogleAccount::first();
>>> $service = app(GoogleAdsService::class);
>>> $service->initialize($account);
>>> $customers = $service->getAccessibleCustomers();
>>> dd($customers);
```

### 6.2 ダッシュボードで確認

1. `/dashboard` にアクセス
2. 広告メトリクスが表示されているか確認
3. エラーが表示される場合はログを確認：

```bash
tail -f storage/logs/laravel.log
```

## トラブルシューティング

### エラー: "developer_token_not_approved"

**原因**: Developer Token が承認されていない

**解決法**:

1. Google Ads 管理画面で Developer Token のステータスを確認
2. 申請がまだ承認中の場合は待つ
3. 申請が拒否された場合は再申請

### エラー: "invalid_customer_id"

**原因**: Customer ID が間違っている

**解決法**:

1. Google Ads 管理画面の Customer ID を確認
2. `.env` の `GOOGLE_ADS_LOGIN_CUSTOMER_ID` がハイフンなしで正しいか確認
3. 10 桁の数字になっているか確認

### エラー: "PERMISSION_DENIED"

**原因**: OAuth スコープが正しく承認されていない

**解決法**:

1. 連携を一度解除して再認証
2. 権限承認画面で **Google Ads** の権限を必ず承認
3. スコープ `https://www.googleapis.com/auth/adwords` が含まれているか確認

### エラー: "Token expired"

**原因**: トークンの有効期限が切れている

**解決法**:

1. システムが自動的にリフレッシュを試みます
2. 解決しない場合は再認証

### エラー: "NO_DATA_FOUND"

**原因**: 広告キャンペーンが存在しない

**解決法**:

1. Google Ads アカウントにキャンペーンが存在するか確認
2. テストアカウントには初期データがないため、テストキャンペーンを作成

## 本番環境での追加設定

本番環境では以下を追加：

1. **Developer Token の申請完了**: 本番環境での使用許可が必要
2. **OAuth 同意画面の本番公開**: Google の審査が必要（最大 4 週間）
3. **追加セキュリティ**: レート制限、エラーハンドリングの強化
4. **モニタリング**: ログとアラートの設定

## 次のステップ

連携が成功したら：

1. **データ同期**: `/accounts/ads` で「同期確認」を実行
2. **レポート生成**: `/reports/generate` でレポートを作成
3. **ダッシュボード確認**: `/dashboard` でメトリクスを確認

## 参考リンク

-   [Google Ads API ドキュメント](https://developers.google.com/google-ads/api/docs/start)
-   [Developer Token 申請](https://ads.google.com/aw/apicenter)
-   [Google Ads API クエリビルダー](https://developers.google.com/google-ads/api/fields/v16/overview)
-   [Laravel Socialite](https://laravel.com/docs/socialite)

## 開発・テスト用のヒント

### テストアカウントを使用する

実際の広告費をかけずに開発できます：

1. [Google Ads テストアカウント](https://developers.google.com/google-ads/api/docs/first-call/overview#test_account) を作成
2. テスト Customer ID を使用
3. Developer Token は即座に発行される

### ダミーデータで開発する

本番 API を使わずに開発したい場合：

1. `GoogleAdsService` でダミーデータを返す実装を追加
2. `.env` に `GOOGLE_ADS_USE_MOCK=true` を追加
3. サービスで環境に応じて切り替え

---

**重要**: Google Ads API は本番環境での使用に制限があります。詳細は[利用規約](https://developers.google.com/google-ads/api/docs/get-started/overview)を確認してください。
