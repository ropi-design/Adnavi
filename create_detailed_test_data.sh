#!/bin/bash

echo "📦 詳細テストデータ（キーワード粒度）を作成します..."

./vendor/bin/sail artisan tinker --execute='
// Google Account
$googleAccount = \App\Models\GoogleAccount::firstOrCreate(
    ["email" => "test@example.com"],
    [
        "user_id" => 1,
        "google_id" => "test-google-id-" . time(),
        "email" => "test@example.com",
        "access_token" => encrypt("dummy-token"),
        "refresh_token" => encrypt("dummy-refresh"),
        "token_expires_at" => now()->addDays(30),
    ]
);
// Ad Account
$adAccount = \App\Models\AdAccount::firstOrCreate(
    ["customer_id" => "123-456-7890"],
    [
        "user_id" => 1,
        "google_account_id" => $googleAccount->id,
        "customer_id" => "123-456-7890",
        "account_name" => "テスト広告アカウント",
        "currency" => "JPY",
        "timezone" => "Asia/Tokyo",
    ]
);
// Analytics Property
$analyticsProperty = \App\Models\AnalyticsProperty::firstOrCreate(
    ["property_id" => "12345678"],
    [
        "user_id" => 1,
        "google_account_id" => $googleAccount->id,
        "property_id" => "12345678",
        "property_name" => "テストAnalyticsプロパティ",
        "timezone" => "Asia/Tokyo",
    ]
);
// Campaigns
$campaignDefs = [
    ["id" => "cmp-brand", "name" => "ブランド指名検索", "status" => "ENABLED"],
    ["id" => "cmp-generic", "name" => "一般ワード検索", "status" => "ENABLED"],
    ["id" => "cmp-competitor", "name" => "競合指名対策", "status" => "ENABLED"],
    ["id" => "cmp-display", "name" => "ディスプレイ", "status" => "ENABLED"],
    ["id" => "cmp-rmkt", "name" => "リマーケティング", "status" => "ENABLED"],
];
$campaigns = collect($campaignDefs)->map(function ($def) use ($adAccount) {
    return \App\Models\Campaign::firstOrCreate(
        ["campaign_id" => $def["id"]],
        [
            "ad_account_id" => $adAccount->id,
            "campaign_id" => $def["id"],
            "campaign_name" => $def["name"],
            "status" => $def["status"],
        ]
    );
});
// Metrics
foreach ($campaigns as $cmp) {
    $kwMap = match ($cmp->campaign_id) {
        "cmp-brand" => ["自社名", "自社名 評判", "自社名 公式", "サービス名", "サービス名 料金"],
        "cmp-generic" => ["オンライン 英会話", "英会話 初心者", "英語 学習 アプリ", "英会話 レッスン", "英語 勉強 法"],
        "cmp-competitor" => ["競合A", "競合A 料金", "競合B", "競合B 口コミ", "競合 サービス 比較"],
        "cmp-display" => ["オーディエンスA", "オーディエンスB", "ターゲットC"],
        "cmp-rmkt" => ["リマーケ ユーザー", "カート放棄", "再訪促進"],
        default => ["汎用 キーワード1", "汎用 キーワード2", "汎用 キーワード3"],
    };
    for ($i = 0; $i < 30; $i++) {
        $baseImp = match ($cmp->campaign_id) {
            "cmp-brand" => 2000, "cmp-generic" => 4000, "cmp-competitor" => 2500, "cmp-display" => 8000, "cmp-rmkt" => 1500,
            default => 3000,
        };
        $imp = max(300, (int)($baseImp * (0.6 + (rand(0, 80) / 100))));
        $ctr = match ($cmp->campaign_id) {
            "cmp-brand" => 0.08, "cmp-generic" => 0.03, "cmp-competitor" => 0.025, "cmp-display" => 0.005, "cmp-rmkt" => 0.02,
            default => 0.02,
        } * (0.8 + (rand(0, 40) / 100));
        $clicks = max(5, (int) round($imp * $ctr));
        $cpc = match ($cmp->campaign_id) {
            "cmp-brand" => 50, "cmp-generic" => 120, "cmp-competitor" => 180, "cmp-display" => 30, "cmp-rmkt" => 90,
            default => 100,
        } * (0.8 + (rand(0, 40) / 100));
        $cost = round($clicks * $cpc, 2);
        $cvr = match ($cmp->campaign_id) {
            "cmp-brand" => 0.05, "cmp-generic" => 0.02, "cmp-competitor" => 0.012, "cmp-display" => 0.006, "cmp-rmkt" => 0.03,
            default => 0.02,
        } * (0.8 + (rand(0, 40) / 100));
        $conversions = round($clicks * $cvr, 2);
        $convValue = round($conversions * (rand(8000, 15000)), 2);
        $cpa = $conversions > 0 ? round($cost / max(0.01, $conversions), 2) : 0;
        $roas = $cost > 0 ? round($convValue / $cost, 4) : 0;
        \App\Models\AdMetricsDaily::updateOrCreate(
            ["campaign_id" => $cmp->id, "date" => now()->subDays($i)->format("Y-m-d")],
            [
                "impressions" => $imp, "clicks" => $clicks, "cost" => $cost, "conversions" => $conversions,
                "conversion_value" => $convValue, "ctr" => round(($clicks > 0 ? $clicks / max(1, $imp) : 0), 4),
                "cpc" => $clicks > 0 ? round($cost / max(1, $clicks), 2) : 0, "cpa" => $cpa, "roas" => $roas,
            ]
        );
        $weights = [];
        foreach (range(0, count($kwMap)-1) as $idx) { $weights[] = 0.6 + ($idx * 0.1); }
        $weightSum = array_sum($weights);
        foreach ($kwMap as $idx => $kw) {
            $share = $weights[$idx] / max(0.001, $weightSum);
            $kwClicks = max(0, (int) round($clicks * $share));
            $kwImps = max(1, (int) round($kwClicks / max(0.001, $ctr)));
            $kwCpc = $cpc * (0.9 + (rand(0, 20) / 100));
            $kwCost = round($kwClicks * $kwCpc, 2);
            $kwCvr = $cvr * (0.7 + (rand(0, 60) / 100));
            $kwConvs = round($kwClicks * $kwCvr, 2);
            $kwConvValue = round($kwConvs * (rand(8000, 15000)), 2);
            \App\Models\KeywordMetricsDaily::updateOrCreate(
                ["campaign_id" => $cmp->id, "date" => now()->subDays($i)->format("Y-m-d"), "keyword" => $kw],
                [
                    "match_type" => in_array($cmp->campaign_id, ["cmp-brand","cmp-generic","cmp-competitor"]) ? "phrase" : "broad",
                    "impressions" => $kwImps, "clicks" => $kwClicks, "cost" => $kwCost, "conversions" => $kwConvs,
                    "conversion_value" => $kwConvValue, "ctr" => $kwImps > 0 ? round($kwClicks / $kwImps, 4) : 0,
                    "cpc" => $kwClicks > 0 ? round($kwCost / max(1, $kwClicks), 2) : 0,
                    "cpa" => $kwConvs > 0 ? round($kwCost / max(0.01, $kwConvs), 2) : 0,
                    "roas" => $kwCost > 0 ? round($kwConvValue / $kwCost, 4) : 0,
                ]
            );
        }
    }
}
for ($i = 0; $i < 30; $i++) {
    \App\Models\AnalyticsMetricsDaily::updateOrCreate(
        ["analytics_property_id" => $analyticsProperty->id, "date" => now()->subDays($i)->format("Y-m-d")],
        ["sessions" => rand(500, 2000), "users" => rand(400, 1500), "bounce_rate" => rand(30, 70), "conversion_rate" => rand(2, 8)]
    );
}
echo "✅ 詳細テストデータ作成完了！";
'

echo ""
echo "✅ 完了！"
echo ""
echo "📝 次のステップ："
echo "1. マイグレーション実行: ./vendor/bin/sail artisan migrate"
echo "2. このスクリプトを実行: bash ./create_detailed_test_data.sh"
echo "3. レポート生成: bash ./test_report_generation.sh"
echo ""


