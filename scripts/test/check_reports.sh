#!/bin/bash

echo "📊 現在のレポート状況を確認"
echo "================================"
echo ""

./vendor/bin/sail artisan tinker --execute="
\$reports = \App\Models\AnalysisReport::all();

echo '📝 レポート一覧:' . PHP_EOL;
echo '--------------------------------' . PHP_EOL;

foreach (\$reports as \$report) {
    echo 'ID: ' . \$report->id;
    echo ' | タイプ: ' . \$report->report_type;
    echo ' | ステータス: ' . \$report->status;
    echo ' | 作成日: ' . \$report->created_at->format('Y-m-d H:i');
    echo PHP_EOL;
    
    if (\$report->error_message) {
        echo '  エラー: ' . substr(\$report->error_message, 0, 100) . '...' . PHP_EOL;
    }
}

echo '' . PHP_EOL;
echo '合計: ' . \$reports->count() . '件' . PHP_EOL;
"

