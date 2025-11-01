# 🚀 Adnavi クイックセットアップ

## 📋 必要な設定

### 1. Gemini API（AI 分析用）- 必須

**一番簡単なので先に設定**

#### 1-1. API キーを取得

https://makersuite.google.com/app/apikey

-   Google アカウントでログイン
-   「Create API Key」をクリック
-   キーをコピー

#### 1-2. .env に設定

```bash
# .envファイルに追加
GEMINI_API_KEY=ここにコピーしたキーを貼り付け
GEMINI_MODEL=gemini-1.5-pro-latest
```

#### 1-3. 設定をクリア

```bash
php artisan config:clear
```

**✅ これで Gemini 分析は使えるようになります！**

---

### 2. Google 連携（広告データ取得用）- オプション

**広告データを取得したい場合のみ**

#### 2-1. Google Cloud Console 設定

1. https://console.cloud.google.com/ にアクセス
2. プロジェクト作成（名前: Adnavi）
3. OAuth 同意画面を設定
    - 「APIs & Services」→「OAuth consent screen」
    - アプリ名: Adnavi
    - テストユーザー: あなたの Gmail
4. OAuth Client ID を作成
    - 「Credentials」→「Create Credentials」→「OAuth Client ID」
    - タイプ: Web application
    - Redirect URI: `http://localhost:8000/auth/google/callback`
    - Client ID と Secret をコピー

#### 2-2. 必要な API を有効化

-   Google Ads API
-   Google Analytics Data API

#### 2-3. .env に設定

```bash
GOOGLE_CLIENT_ID=あなたのClient ID
GOOGLE_CLIENT_SECRET=あなたのClient Secret
```

#### 2-4. Socialite をインストール

```bash
composer require laravel/socialite
```

#### 2-5. 設定をクリア

```bash
php artisan config:clear
```

**✅ これで Google 連携が使えます！**

---

## 📝 設定後の手順

### 1. マイグレーション実行

```bash
php artisan migrate
```

### 2. キューを起動（別ターミナル）

```bash
php artisan queue:work
```

### 3. サーバー起動（別ターミナル）

```bash
php artisan serve
```

### 4. ブラウザでアクセス

http://localhost:8000

---

## 🎯 最小限の設定（試すだけなら）

Gemini だけ設定すれば、AI 分析は使えます：

```bash
# 1. Gemini APIキーを取得（無料）
# https://makersuite.google.com/app/apikey

# 2. .envに追加
GEMINI_API_KEY=your-key-here

# 3. 実行
php artisan migrate
php artisan config:clear

# 4. キューを起動
php artisan queue:work

# 5. ブラウザで試す
# http://localhost:8000
```

これで**レポート生成 →Gemini 分析 → インサイト・改善施策の生成**が動作します！

---

詳細は各ドキュメントを参照：

-   [docs/GEMINI_SETUP.md](docs/GEMINI_SETUP.md) - Gemini 設定
-   [docs/GOOGLE_CONNECT_SETUP.md](docs/GOOGLE_CONNECT_SETUP.md) - Google 連携設定
