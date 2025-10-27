#!/bin/bash

echo "🔄 SQLiteからMariaDBへ切り替えます..."
echo ""

# Step 1: MariaDB起動
echo "📦 Step 1: MariaDBコンテナを起動..."
./vendor/bin/sail up -d

echo ""
echo "⏳ MariaDBの起動を待っています（30秒）..."
sleep 30

# Step 2: キャッシュクリア
echo ""
echo "🧹 Step 2: キャッシュをクリア..."
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan cache:clear

# Step 3: マイグレーション
echo ""
echo "📊 Step 3: データベースマイグレーション..."
./vendor/bin/sail artisan migrate:fresh --force

if [ $? -ne 0 ]; then
    echo "❌ マイグレーションに失敗しました"
    echo ""
    echo "確認事項："
    echo "1. .envファイルのDB設定が正しいか"
    echo "2. MariaDBコンテナが起動しているか: docker-compose ps"
    echo "3. MariaDBログを確認: docker-compose logs mariadb"
    exit 1
fi

# Step 4: ユーザー作成
echo ""
echo "👤 Step 4: テストユーザーを作成..."
./vendor/bin/sail artisan tinker --execute="
\App\Models\User::firstOrCreate(
    ['email' => 'test@example.com'],
    [
        'name' => 'テストユーザー',
        'email' => 'test@example.com',
        'password' => \Illuminate\Support\Facades\Hash::make('password'),
        'email_verified_at' => now(),
    ]
);
echo '✅ ユーザー作成完了！';
"

echo ""
echo "✅ MariaDBへの切り替えが完了しました！"
echo ""
echo "📝 ログイン情報："
echo "   メール: test@example.com"
echo "   パスワード: password"
echo ""
echo "🌐 ブラウザでアクセス: http://localhost/login"
echo ""

