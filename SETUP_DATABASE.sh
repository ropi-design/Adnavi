#!/bin/bash

echo "🗄️ データベースをセットアップします..."

# マイグレーション実行
echo "📦 マイグレーション実行中..."
php artisan migrate --force

echo ""
echo "👤 テストユーザーを作成中..."
php artisan tinker --execute="
\App\Models\User::firstOrCreate(
    ['email' => 'test@example.com'],
    [
        'name' => 'テストユーザー',
        'email' => 'test@example.com',
        'password' => \Illuminate\Support\Facades\Hash::make('password'),
        'email_verified_at' => now(),
    ]
);
echo '✅ ユーザー作成完了！\\nメール: test@example.com\\nパスワード: password';
"

echo ""
echo "✅ セットアップ完了！"
echo ""
echo "📝 ログイン情報："
echo "   メール: test@example.com"
echo "   パスワード: password"
echo ""

