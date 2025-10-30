#!/bin/bash

echo "📦 テストデータを作成します..."

./vendor/bin/sail artisan tinker --execute="
// Google Accountを作成
\$googleAccount = \App\Models\GoogleAccount::firstOrCreate(
    ['email' => 'test@example.com'],
    [
        'user_id' => 1,
        'google_id' => 'test-google-id-' . time(),
        'email' => 'test@example.com',
        'access_token' => encrypt('dummy-token'),
        'refresh_token' => encrypt('dummy-refresh'),
        'token_expires_at' => now()->addDays(30),
    ]
);

// 広告アカウントを作成
\$adAccount = \App\Models\AdAccount::firstOrCreate(
    ['customer_id' => '123-456-7890'],
    [
        'user_id' => 1,
        'google_account_id' => \$googleAccount->id,
        'customer_id' => '123-456-7890',
        'account_name' => 'テスト広告アカウント',
        'currency' => 'JPY',
        'timezone' => 'Asia/Tokyo',
    ]
);

// Analyticsプロパティを作成
\$analyticsProperty = \App\Models\AnalyticsProperty::firstOrCreate(
    ['property_id' => '12345678'],
    [
        'user_id' => 1,
        'google_account_id' => \$googleAccount->id,
        'property_id' => '12345678',
        'property_name' => 'テストAnalyticsプロパティ',
        'timezone' => 'Asia/Tokyo',
    ]
);

// テストキャンペーンを作成
\$campaign = \App\Models\Campaign::firstOrCreate(
    ['campaign_id' => 'test-campaign-123'],
    [
        'ad_account_id' => \$adAccount->id,
        'campaign_id' => 'test-campaign-123',
        'campaign_name' => 'テストキャンペーン',
        'status' => 'ENABLED',
    ]
);

// ダミーの広告メトリクスを作成
for (\$i = 0; \$i < 30; \$i++) {
    \App\Models\AdMetricsDaily::updateOrCreate(
        [
            'campaign_id' => \$campaign->id,
            'date' => now()->subDays(\$i)->format('Y-m-d'),
        ],
        [
            'impressions' => rand(1000, 5000),
            'clicks' => rand(50, 200),
            'cost' => rand(5000, 20000),
            'conversions' => rand(5, 30),
            'conversion_value' => rand(50000, 200000),
        ]
    );
}

// ダミーのAnalyticsメトリクスを作成
for (\$i = 0; \$i < 30; \$i++) {
    \App\Models\AnalyticsMetricsDaily::updateOrCreate(
        [
            'analytics_property_id' => \$analyticsProperty->id,
            'date' => now()->subDays(\$i)->format('Y-m-d'),
        ],
        [
            'sessions' => rand(500, 2000),
            'users' => rand(400, 1500),
            'bounce_rate' => rand(30, 70),
            'conversion_rate' => rand(2, 8),
        ]
    );
}

echo '✅ テストデータ作成完了！';
"

echo ""
echo "✅ 完了！"
echo ""
echo "📝 次のステップ："
echo "1. ブラウザで http://localhost/reports にアクセス"
echo "2. 「レポート生成」ボタンをクリック"
echo "3. 以下を選択："
echo "   - レポートタイプ: 週次"
echo "   - 広告アカウント: テスト広告アカウント"
echo "   - Analyticsプロパティ: テストAnalyticsプロパティ"
echo "4. 「生成開始」をクリック"
echo ""

