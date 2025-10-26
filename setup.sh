#!/bin/bash

echo "🚀 Adnavi セットアップスクリプト"
echo "================================"
echo ""

# データベースファイルの確認・作成
echo "📁 データベースファイルを確認中..."
if [ ! -f database/database.sqlite ]; then
    echo "   データベースファイルを作成します..."
    touch database/database.sqlite
    echo "   ✅ 作成完了"
else
    echo "   ✅ 既に存在します"
fi
echo ""

# .envファイルの確認
echo "⚙️  環境設定を確認中..."
if [ ! -f .env ]; then
    echo "   .envファイルをコピーします..."
    cp .env.example .env
    echo "   ✅ コピー完了"
else
    echo "   ✅ 既に存在します"
fi
echo ""

# アプリケーションキーの生成
echo "🔑 アプリケーションキーを確認中..."
if grep -q "APP_KEY=$" .env || ! grep -q "APP_KEY=" .env; then
    echo "   キーを生成します..."
    php artisan key:generate
    echo "   ✅ 生成完了"
else
    echo "   ✅ 既に設定されています"
fi
echo ""

# マイグレーションの実行
echo "🗄️  データベースをセットアップ中..."
php artisan migrate --force
if [ $? -eq 0 ]; then
    echo "   ✅ マイグレーション完了"
else
    echo "   ⚠️  マイグレーションでエラーが発生しました"
fi
echo ""

# node_modulesの確認
echo "📦 Node.jsパッケージを確認中..."
if [ ! -d node_modules ]; then
    echo "   パッケージをインストールします..."
    npm install
    echo "   ✅ インストール完了"
else
    echo "   ✅ 既にインストールされています"
fi
echo ""

echo "================================"
echo "✅ セットアップ完了！"
echo ""
echo "サーバーを起動するには："
echo ""
echo "  ターミナル1:"
echo "  $ php artisan serve"
echo ""
echo "  ターミナル2:"
echo "  $ npm run dev"
echo ""
echo "その後、ブラウザで http://localhost:8000 にアクセス"
echo ""

