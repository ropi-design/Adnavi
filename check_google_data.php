<?php

/**
 * Googleアカウント連携後のデータ確認スクリプト
 * 
 * 使用方法:
 * php check_google_data.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\GoogleAccount;
use App\Models\AnalyticsProperty;

echo "=== Google Accounts (google_accounts テーブル) ===\n";
echo "このテーブルには必ずデータが入っているはずです。\n\n";

$googleAccounts = GoogleAccount::with('user')->get();

if ($googleAccounts->isEmpty()) {
    echo "❌ データがありません。Googleアカウントの連携を確認してください。\n";
} else {
    echo "✓ データが存在します (" . $googleAccounts->count() . " 件)\n\n";
    foreach ($googleAccounts as $account) {
        echo "  ID: {$account->id}\n";
        echo "  User ID: {$account->user_id}\n";
        echo "  User Name: {$account->user->name}\n";
        echo "  Email: {$account->email}\n";
        echo "  Google ID: {$account->google_id}\n";
        echo "  Token Valid: " . ($account->isTokenValid() ? 'Yes' : 'No') . "\n";
        echo "  Token Expires: " . ($account->token_expires_at ? $account->token_expires_at->format('Y-m-d H:i:s') : 'N/A') . "\n";
        echo "  Created: {$account->created_at->format('Y-m-d H:i:s')}\n";
        echo "  ---\n";
    }
}

echo "\n=== Analytics Properties (analytics_properties テーブル) ===\n";
echo "このテーブルには、Analyticsプロパティが取得できた場合に入ります。\n";
echo "（連携直後に取得に失敗しても、後で手動で同期できます）\n\n";

$analyticsProperties = AnalyticsProperty::with(['googleAccount', 'user'])->get();

if ($analyticsProperties->isEmpty()) {
    echo "⚠️  データがありません。以下の可能性があります:\n";
    echo "  1. Analyticsプロパティの同期処理が失敗した\n";
    echo "  2. 連携したGoogleアカウントにAnalyticsプロパティが存在しない\n";
    echo "  3. Analytics APIの権限が不足している\n";
    echo "\n";
    echo "  確認方法:\n";
    echo "  - storage/logs/laravel.log を確認\n";
    echo "  - Google Cloud ConsoleでAnalytics Admin APIが有効になっているか確認\n";
} else {
    echo "✓ データが存在します (" . $analyticsProperties->count() . " 件)\n\n";
    foreach ($analyticsProperties as $property) {
        echo "  ID: {$property->id}\n";
        echo "  User ID: {$property->user_id}\n";
        echo "  User Name: {$property->user->name}\n";
        echo "  Property ID: {$property->property_id}\n";
        echo "  Property Name: {$property->property_name}\n";
        echo "  Timezone: {$property->timezone}\n";
        echo "  Active: " . ($property->is_active ? 'Yes' : 'No') . "\n";
        echo "  Google Account: {$property->googleAccount->email}\n";
        echo "  Last Synced: " . ($property->last_synced_at ? $property->last_synced_at->format('Y-m-d H:i:s') : 'N/A') . "\n";
        echo "  Created: {$property->created_at->format('Y-m-d H:i:s')}\n";
        echo "  ---\n";
    }
}

echo "\n=== まとめ ===\n";
echo "1. google_accounts テーブル: " . ($googleAccounts->isEmpty() ? '❌ データなし' : '✓ データあり') . "\n";
echo "2. analytics_properties テーブル: " . ($analyticsProperties->isEmpty() ? '⚠️  データなし（手動で同期が必要な可能性あり）' : '✓ データあり') . "\n";
