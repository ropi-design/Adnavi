# ログイン問題の修正方法

## 問題

ログイン画面でログインできない状態

## 原因

Fortify のルートが正しく読み込まれていない可能性

## 解決方法

### 1. テストユーザーを作成

ターミナルで以下を実行：

```bash
# tinkerでテストユーザーを作成
php artisan tinker
```

tinker で以下を入力：

```php
\App\Models\User::firstOrCreate(
    ['email' => 'test@example.com'],
    [
        'name' => 'テストユーザー',
        'email' => 'test@example.com',
        'password' => \Illuminate\Support\Facades\Hash::make('password'),
        'email_verified_at' => now(),
    ]
);
```

エラーが出ない場合は成功

### 2. キャッシュをクリア

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

### 3. ログインを試す

1. ブラウザで http://localhost:8000/login にアクセス
2. メール: test@example.com
3. パスワード: password
4. ログインボタンをクリック

### 4. まだログインできない場合

ルートを確認：

```bash
php artisan route:list | grep login
```

Fortify ルートが表示されない場合は、以下を実行：

```bash
composer dump-autoload
php artisan config:clear
php artisan route:clear
```

### 5. 新規登録から試す

ログインできない場合は、新規登録から試してください：

1. http://localhost:8000/register にアクセス
2. 名前、メール、パスワードを入力
3. 登録後、自動的にログインされます

## デバッグ

ログを確認：

```bash
tail -f storage/logs/laravel.log
```

ブラウザのコンソール（F12）でエラーを確認

## まとめ

まず以下を試してください：

1. テストユーザー作成（上記の tinker コマンド）
2. キャッシュクリア
3. http://localhost:8000/login でログイン
4. メール: test@example.com / パスワード: password
