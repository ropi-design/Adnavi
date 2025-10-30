#!/bin/bash

echo "🧹 失敗したレポートを削除して再テスト"
echo "================================"
echo ""

# 失敗したレポートを削除
echo "1️⃣ 失敗したレポートを削除中..."
./vendor/bin/sail artisan tinker --execute="
\App\Models\AnalysisReport::where('status', 'failed')->delete();
echo '✅ 失敗したレポートを削除しました' . PHP_EOL;
"

echo ""
echo "2️⃣ 新しいレポートを生成中..."
./vendor/bin/sail artisan tinker --execute="
\$report = \App\Models\AnalysisReport::create([
    'user_id' => 1,
    'ad_account_id' => 1,
    'analytics_property_id' => 1,
    'report_type' => 'weekly',
    'start_date' => now()->subWeek(),
    'end_date' => now(),
    'status' => 'pending',
]);

echo '📝 レポート作成（ID: ' . \$report->id . '）' . PHP_EOL;
echo '' . PHP_EOL;

try {
    echo '🤖 Gemini APIで分析を開始...' . PHP_EOL;
    \App\Jobs\GenerateAnalysisReport::dispatchSync(\$report->id);
    \$report->refresh();
    
    echo '' . PHP_EOL;
    echo '✅ レポート生成完了！' . PHP_EOL;
    echo '' . PHP_EOL;
    echo '📊 結果:' . PHP_EOL;
    echo '  - ステータス: ' . \$report->status->value . PHP_EOL;
    echo '  - 総合スコア: ' . (\$report->overall_score ?? 'N/A') . PHP_EOL;
    echo '  - インサイト数: ' . \$report->insights()->count() . PHP_EOL;
    echo '  - 改善施策数: ' . \$report->recommendations()->count() . PHP_EOL;
    
    if (\$report->status->value === 'failed') {
        echo '' . PHP_EOL;
        echo '❌ エラー: ' . \$report->error_message . PHP_EOL;
    }
} catch (\Exception \$e) {
    echo '' . PHP_EOL;
    echo '❌ エラーが発生: ' . \$e->getMessage() . PHP_EOL;
}
"

echo ""
echo "================================"
echo "✅ 完了！"
echo ""
echo "📝 ブラウザで確認："
echo "   http://localhost/reports"
echo ""

