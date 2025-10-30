#!/bin/bash

echo "🗑️  テストGoogleアカウントデータを削除します..."

./vendor/bin/sail artisan tinker --execute="
\App\Models\GoogleAccount::where('email', 'test@example.com')->delete();
echo '✅ テストGoogleアカウントを削除しました';
"

echo ""
echo "✅ 完了！"
echo ""
echo "📝 次のステップ："
echo "1. ブラウザをリロードしてください"
echo "2. 「Googleアカウントと連携する」ボタンが表示されます"
echo "3. ボタンをクリックして実際のGoogle認証を試せます"
echo ""

