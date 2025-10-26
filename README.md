# Adnavi - AI 駆動型広告分析プラットフォーム

Google 広告と Google アナリティクスのデータを統合し、Gemini AI による自動分析と改善提案を行う Laravel アプリケーションです。

## 🚀 クイックスタート

### 1. セットアップ（初回のみ）

```bash
# プロジェクトディレクトリに移動
cd /Users/satohiro/camp/100_laravel/Adnavi

# データベースセットアップ
php artisan migrate

# テストユーザー作成
php artisan tinker
>>> User::create(['name' => 'テスト', 'email' => 'test@example.com', 'password' => Hash::make('password')]);
>>> exit
```

### 2. サーバー起動

```bash
# Sailで起動
./vendor/bin/sail up -d

# または直接起動
php artisan serve
```

### 3. アクセス

```
http://localhost
```

**ログイン情報:**

-   Email: `test@example.com`
-   Password: `password`

## 📱 利用可能な画面

| 画面           | URL                | 説明                               |
| -------------- | ------------------ | ---------------------------------- |
| ダッシュボード | `/dashboard`       | メトリクスサマリー、トレンド表示   |
| レポート一覧   | `/reports`         | 分析レポート一覧、検索・フィルター |
| インサイト一覧 | `/insights`        | AI 抽出インサイト、優先度別表示    |
| 改善施策一覧   | `/recommendations` | 施策提案、ステータス管理           |
| Google 連携    | `/accounts/google` | OAuth 認証、連携管理               |

## 🎯 主な機能

### 実装済み ✅

-   データベース設計（9 テーブル）
-   Eloquent モデル（10 個＋リレーション）
-   Volt コンポーネント（5 個）
-   日本語バリデーション
-   Flux UI 統合
-   レスポンシブデザイン
-   Google OAuth 基盤
-   Gemini AI 基盤

### 実装予定 ⏳

-   Google Ads API 連携
-   Google Analytics API 連携
-   レポート生成ジョブ
-   データ同期スケジューラー

## 🛠️ 技術スタック

-   **Laravel 12**
-   **Livewire Volt 3** - シングルファイルコンポーネント
-   **Flux UI** - モダンな UI コンポーネント
-   **Tailwind CSS**
-   **MariaDB** - データベース（Docker 内）
-   **Google API** - Ads, Analytics, Gemini

## 📚 ドキュメント

-   [アーキテクチャ設計](docs/ARCHITECTURE.md)
-   [インストール手順](docs/INSTALLATION_GUIDE.md)
-   [実装ロードマップ](docs/IMPLEMENTATION_ROADMAP.md)
-   [Volt 実装ガイド](docs/LIVEWIRE_VOLT_GUIDE.md)
-   [実装状況](docs/IMPLEMENTATION_STATUS.md)
-   [実装サマリー](IMPLEMENTATION_SUMMARY.md)

## 🔧 開発コマンド

```bash
# マイグレーション
php artisan migrate

# Tinker
php artisan tinker

# ルート確認
php artisan route:list

# キャッシュクリア
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## 📝 プロジェクト構成

```
Adnavi/
├── app/
│   ├── Enums/         # Enumクラス（5個）
│   ├── Models/        # Eloquentモデル（10個）
│   ├── Services/      # サービスクラス
│   └── Http/          # コントローラー
├── database/
│   └── migrations/    # マイグレーション（9個）
├── resources/
│   └── views/
│       ├── livewire/  # Voltコンポーネント
│       ├── pages/     # ページテンプレート
│       └── layouts/   # レイアウト
├── config/            # 設定ファイル
└── docs/              # ドキュメント
```

## 🎉 実装進捗

-   ✅ データベース設計: 100%
-   ✅ モデル・Enum: 100%
-   ✅ UI 実装: 80%
-   ⏳ API 連携: 基盤のみ
-   ⏳ AI 統合: 基盤のみ

## 📖 次のステップ

1. Google Cloud Console で認証情報取得
2. `.env`に API キーを設定
3. 実際のデータ連携テスト
4. 本番環境デプロイ

## 💡 トラブルシューティング

### サーバーが起動しない

```bash
# Sailで起動
./vendor/bin/sail up -d

# ポート80で実行される
```

### データベースエラー

```bash
# マイグレーション再実行
php artisan migrate:fresh
```

### キャッシュエラー

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

**Happy Coding! 🚀**
