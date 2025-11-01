# 🚀 ローカルサーバー起動手順

## すぐに見られるようにする（5 分）

### 1. データベースファイルの確認・作成

```bash
# Adnaviディレクトリに移動
cd /Users/satohiro/camp/100_laravel/Adnavi

# データベースファイルが存在するか確認
ls -la database/database.sqlite

# 存在しない場合は作成
touch database/database.sqlite
```

### 2. アプリケーションキーの確認

```bash
# .envファイルにAPP_KEYがあるか確認
cat .env | grep APP_KEY

# 空の場合は生成
php artisan key:generate
```

### 3. マイグレーションの実行

```bash
# データベースをセットアップ
php artisan migrate

# 実行結果に以下のようなテーブルが表示されればOK:
# - create users table
# - create google_accounts table
# - create ad_accounts table
# ... など
```

### 4. 開発サーバーの起動

**ターミナル 1（Laravel サーバー）:**

```bash
php artisan serve

# 以下のように表示されればOK:
# INFO  Server running on [http://127.0.0.1:8000]
```

**ターミナル 2（Vite - CSS/JS ビルド）:**

```bash
npm run dev

# 以下のように表示されればOK:
# VITE v5.x.x  ready in xxx ms
# ➜  Local:   http://localhost:5173/
```

### 5. ブラウザでアクセス

```
http://localhost:8000
```

または

```
http://127.0.0.1:8000
```

## 🎯 動作確認

### ログインが必要な場合

まずテストユーザーを作成：

```bash
php artisan tinker

# Tinker内で実行:
User::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => Hash::make('password')
]);

exit
```

ログイン情報：

-   Email: `test@example.com`
-   Password: `password`

## ❌ トラブルシューティング

### エラー: "No application encryption key has been specified"

```bash
php artisan key:generate
```

### エラー: "SQLSTATE[HY000]: General error: 1 no such table"

```bash
# マイグレーションを実行
php artisan migrate

# それでもダメなら
php artisan migrate:fresh
```

### エラー: Vite がビルドできない

```bash
# node_modulesを再インストール
rm -rf node_modules
npm install
npm run dev
```

### ポート 8000 が使用中の場合

```bash
# 別のポートで起動
php artisan serve --port=8001

# ブラウザでアクセス
# http://localhost:8001
```

## 📱 見られる画面

現在実装済み：

1. **ログイン画面** - `/login`
2. **ダッシュボード** - `/dashboard` (ログイン後)
    - 6 つのメトリクスカード
    - 期間フィルター
    - クイックアクション
3. **Google 連携** - `/accounts/google`
    - 連携状態表示
    - 接続/解除ボタン

## 🎨 見た目のチェックポイント

✅ Flux UI コンポーネントが正しく表示される
✅ サイドバーナビゲーションが機能する
✅ メトリクスカードがグリッド表示される
✅ ボタンのホバー効果が動作する
✅ レスポンシブデザイン（モバイル対応）

## 🔧 開発中に便利なコマンド

```bash
# ルート一覧を確認
php artisan route:list

# キャッシュをクリア
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# データベースをリセット
php artisan migrate:fresh

# Tinkerでデータを確認
php artisan tinker
>>> User::count()
>>> User::first()
```

## 次のステップ

サーバーが起動したら：

1. ダッシュボードの見た目を確認
2. Google 連携画面を確認
3. サイドバーのナビゲーションを試す
4. モバイルビューも確認（ブラウザのデベロッパーツール）

問題があれば、エラーメッセージを教えてください！
