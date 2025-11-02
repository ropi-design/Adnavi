# theme カラムエラーの修正

## 問題

```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'theme' in 'SET'
```

`users` テーブルに `theme` カラムが存在しない場合にこのエラーが発生します。

## 原因

マイグレーションファイルは存在するものの、まだ実行されていない可能性があります。

## 解決方法

### 方法 1: マイグレーションコマンドを実行（推奨）

```bash
php artisan migrate
```

### 方法 2: 特定のマイグレーションファイルのみ実行

```bash
php artisan migrate --path=database/migrations/2025_01_21_000000_add_theme_to_users_table.php
```

### 方法 3: Artisan コマンドを実行

```bash
php artisan migrate:all-now
```

### 方法 4: スタンドアロンスクリプトを実行

```bash
php run_all_migrations.php
```

### 方法 5: 直接 SQL を実行

マイグレーションが実行できない場合は、直接 SQL を実行してください：

```sql
ALTER TABLE `users` ADD COLUMN `theme` VARCHAR(255) DEFAULT 'dark' AFTER `email_verified_at`;
```

または `email_verified_at` カラムが存在しない場合：

```sql
ALTER TABLE `users` ADD COLUMN `theme` VARCHAR(255) DEFAULT 'dark' AFTER `email`;
```

## 修正内容

### 1. マイグレーションファイルの修正

`database/migrations/2025_01_21_000000_add_theme_to_users_table.php` を修正しました：

-   カラムの存在確認を追加
-   Schema ファサードの制限を回避するために、生の SQL 文を使用

### 2. Livewire コンポーネントの修正

`resources/views/livewire/settings/appearance.blade.php` を修正しました：

-   `Schema::hasColumn()` を使用してカラムの存在を確認
-   カラムが存在しない場合でもエラーが発生しないように防御的コーディングを追加

### 3. レイアウトコンポーネントの修正

`resources/views/components/layouts/app/header.blade.php` と `sidebar.blade.php` を修正しました：

-   `Schema::hasColumn()` を使用してカラムの存在を確認
-   カラムが存在しない場合はデフォルト値 'dark' を使用

## 確認方法

マイグレーションが正常に実行されたか確認：

```bash
# マイグレーションの状態を確認
php artisan migrate:status

# users テーブルの構造を確認
php artisan tinker
Schema::getColumnListing('users');
```

または直接データベースに接続して確認：

```sql
DESCRIBE users;
```

## 注意事項

-   マイグレーションを実行する前に、データベースのバックアップを取ることを推奨します
-   本番環境では、必ず `php artisan migrate` を使用してください
-   開発環境では `migrate:fresh` や `migrate:refresh` を使用することもできます

## 関連ファイル

-   `database/migrations/2025_01_21_000000_add_theme_to_users_table.php`
-   `resources/views/livewire/settings/appearance.blade.php`
-   `resources/views/components/layouts/app/header.blade.php`
-   `resources/views/components/layouts/app/sidebar.blade.php`
-   `app/Models/User.php`
-   `run_all_migrations.php`
-   `app/Console/Commands/RunAllMigrations.php`
