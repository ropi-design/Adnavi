# SQLite から MariaDB への切り替え方法

## 🔄 切り替え手順

### ステップ 1: .env 設定を確認

`.env`ファイルの DB 設定を以下に変更：

```env
DB_CONNECTION=mariadb
DB_HOST=mariadb
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
```

### ステップ 2: MariaDB コンテナを起動

```bash
./vendor/bin/sail up -d
```

または

```bash
docker-compose up -d mariadb
```

### ステップ 3: MariaDB 接続を待つ

コンテナが完全に起動するまで 30 秒ほど待ちます：

```bash
echo "MariaDBの起動を待っています..."
sleep 30
```

### ステップ 4: マイグレーション実行

```bash
./vendor/bin/sail artisan migrate:fresh
```

または（Sail を使わない場合）

```bash
php artisan migrate:fresh
```

### ステップ 5: テストユーザー作成

```bash
./vendor/bin/sail artisan tinker --execute="
\App\Models\User::create([
    'name' => 'テストユーザー',
    'email' => 'test@example.com',
    'password' => \Illuminate\Support\Facades\Hash::make('password'),
    'email_verified_at' => now(),
]);
echo 'ユーザー作成完了！';
"
```

## 🚀 ワンコマンド実行

すべてを一度に実行：

```bash
./vendor/bin/sail up -d && \
sleep 30 && \
./vendor/bin/sail artisan migrate:fresh --force && \
./vendor/bin/sail artisan tinker --execute="\App\Models\User::create(['name'=>'テストユーザー','email'=>'test@example.com','password'=>\Illuminate\Support\Facades\Hash::make('password'),'email_verified_at'=>now()]);"
```

## 🔙 SQLite に戻す場合

```bash
# .envを編集
DB_CONNECTION=sqlite

# マイグレーション実行
php artisan migrate:fresh

# ユーザー作成
php artisan tinker --execute="\App\Models\User::create(['name'=>'テストユーザー','email'=>'test@example.com','password'=>\Illuminate\Support\Facades\Hash::make('password'),'email_verified_at'=>now()]);"
```

## 📝 ログイン情報

```
メール: test@example.com
パスワード: password
```

## ⚠️ 注意事項

1. **Sail を使用する場合**

    - すべてのコマンドは `./vendor/bin/sail` で実行
    - 例: `./vendor/bin/sail artisan migrate`

2. **Sail を使用しない場合**

    - ローカルに MariaDB/MySQL が必要
    - .env の`DB_HOST`を`127.0.0.1`に変更

3. **データは消える**
    - `migrate:fresh`は既存データをすべて削除
    - バックアップが必要な場合は先に実行

## 🐳 Docker 確認コマンド

```bash
# コンテナ状態確認
docker-compose ps

# MariaDBログ確認
docker-compose logs mariadb

# コンテナ再起動
docker-compose restart mariadb
```
