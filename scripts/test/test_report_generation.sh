#!/bin/bash

echo "📊 AIレポート生成テスト"
echo "================================"
echo ""

# ステップ1: テストデータがあるか確認
echo "🔍 ステップ1: テストデータの確認..."
./vendor/bin/sail artisan tinker --execute="
\$googleAccount = \App\Models\GoogleAccount::first();
\$adAccount = \App\Models\AdAccount::first();
\$analyticsProperty = \App\Models\AnalyticsProperty::first();
\$campaign = \App\Models\Campaign::first();
\$metricsCount = \App\Models\AdMetricsDaily::count();

if (!\$googleAccount || !\$adAccount || !\$campaign || \$metricsCount == 0) {
    echo '❌ テストデータが不足しています。create_test_data.shを実行してください。' . PHP_EOL;
    exit(1);
}

echo '✅ テストデータ確認完了:' . PHP_EOL;
echo '  - Googleアカウント: ' . \$googleAccount->email . PHP_EOL;
echo '  - 広告アカウント: ' . \$adAccount->account_name . PHP_EOL;
echo '  - キャンペーン: ' . \$campaign->campaign_name . PHP_EOL;
echo '  - 広告メトリクス: ' . \$metricsCount . '件' . PHP_EOL;
if (\$analyticsProperty) {
    echo '  - Analyticsプロパティ: ' . \$analyticsProperty->property_name . PHP_EOL;
}
"

echo ""
echo "🚀 ステップ2: レポート生成ジョブを実行..."
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

echo '✅ レポートレコードを作成しました（ID: ' . \$report->id . '）' . PHP_EOL;
echo '' . PHP_EOL;

// ジョブを同期実行
echo '🤖 Gemini APIで分析を開始...' . PHP_EOL;
try {
    \App\Jobs\GenerateAnalysisReport::dispatchSync(\$report->id);
    \$report->refresh();
    
    echo '✅ レポート生成完了！' . PHP_EOL;
    echo '' . PHP_EOL;
    echo '📈 結果:' . PHP_EOL;
    echo '  - ステータス: ' . \$report->status->value . PHP_EOL;
    echo '  - 総合スコア: ' . \$report->overall_score . PHP_EOL;
    echo '  - インサイト数: ' . \$report->insights()->count() . PHP_EOL;
    echo '  - 改善施策数: ' . \$report->recommendations()->count() . PHP_EOL;
    
    if (\$report->status->value === 'failed') {
        echo '❌ エラーメッセージ: ' . \$report->error_message . PHP_EOL;
    }
} catch (\Exception \$e) {
    echo '❌ エラーが発生しました: ' . \$e->getMessage() . PHP_EOL;
    echo '' . PHP_EOL;
    echo 'スタックトレース:' . PHP_EOL;
    echo \$e->getTraceAsString() . PHP_EOL;
}
"

echo ""
echo "================================"
echo "📝 次のステップ:"
echo "1. ブラウザで http://localhost/reports にアクセス"
echo "2. 生成されたレポートを確認"
echo "3. レポート詳細をクリックしてインサイトと施策を確認"
echo ""

