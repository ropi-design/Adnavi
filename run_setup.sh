#!/bin/bash

echo "🚀 Gemini分析機能のセットアップを開始します..."
echo ""

# マイグレーション実行
echo "📦 データベースマイグレーションを実行中..."
php artisan migrate

echo ""
echo "✅ マイグレーション完了！"
echo ""
echo "📝 次のステップ："
echo "1. .envファイルに以下を追加："
echo "   GEMINI_API_KEY=あなたのAPIキー"
echo ""
echo "2. 設定をクリア："
echo "   php artisan config:clear"
echo ""
echo "3. 新しいターミナルでキューを起動："
echo "   php artisan queue:work"
echo ""
echo "4. ブラウザで http://localhost:8000/reports にアクセス"
echo ""

