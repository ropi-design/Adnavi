#!/bin/bash

echo "🔧 Google OAuth の設定を .env に追加します"
echo ""

# .env ファイルの存在確認
if [ ! -f .env ]; then
    echo "❌ .env ファイルが見つかりません"
    exit 1
fi

# 既に設定があるかチェック
if grep -q "GOOGLE_CLIENT_ID" .env; then
    echo "⚠️  既に Google OAuth の設定が存在します"
    echo ""
    echo "現在の設定:"
    grep -E "^GOOGLE_CLIENT_ID|^GOOGLE_CLIENT_SECRET|^GOOGLE_REDIRECT_URI" .env
    echo ""
    read -p "上書きしますか？ (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "❌ キャンセルしました"
        exit 1
    fi
    # 既存の設定を削除
    sed -i.bak '/^GOOGLE_CLIENT_ID/d' .env
    sed -i.bak '/^GOOGLE_CLIENT_SECRET/d' .env
    sed -i.bak '/^GOOGLE_REDIRECT_URI/d' .env
    sed -i.bak '/^# Google OAuth/d' .env
fi

echo ""
echo "📝 Google Cloud Console で取得した情報を入力してください"
echo ""

# Client ID の入力
read -p "GOOGLE_CLIENT_ID を入力: " client_id
if [ -z "$client_id" ]; then
    echo "❌ Client ID が入力されていません"
    exit 1
fi

# Client Secret の入力
read -p "GOOGLE_CLIENT_SECRET を入力: " client_secret
if [ -z "$client_secret" ]; then
    echo "❌ Client Secret が入力されていません"
    exit 1
fi

# Redirect URI の入力（デフォルト値あり）
read -p "Redirect URI (デフォルト: http://localhost/auth/google/callback): " redirect_uri
if [ -z "$redirect_uri" ]; then
    redirect_uri="http://localhost/auth/google/callback"
fi

# .env に追記
echo "" >> .env
echo "# Google OAuth" >> .env
echo "GOOGLE_CLIENT_ID=$client_id" >> .env
echo "GOOGLE_CLIENT_SECRET=$client_secret" >> .env
echo "GOOGLE_REDIRECT_URI=$redirect_uri" >> .env

echo ""
echo "✅ .env ファイルに設定を追加しました！"
echo ""
echo "📝 次のステップ："
echo "1. キャッシュをクリア:"
echo "   ./vendor/bin/sail artisan config:clear"
echo ""
echo "2. テストデータを削除（まだの場合）:"
echo "   ./clear_test_google_account.sh"
echo ""
echo "3. ブラウザで http://localhost/accounts/google にアクセスして連携を試す"
echo ""

