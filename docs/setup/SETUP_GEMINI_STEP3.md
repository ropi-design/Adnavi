# ステップ 3: テストデータ作成とレポート生成

## 📝 テスト用のダミーデータを作成

実際の Google 連携がなくても、ダミーデータで Gemini 分析をテストできます。

## 🚀 実行方法

### 1. テストデータ作成スクリプト

以下のコマンドを実行：

```bash
./vendor/bin/sail artisan tinker
```

tinker で以下を実行：

```php
// Google Accountを作成
$googleAccount = \App\Models\GoogleAccount::create([
    'user_id' => 1,
    'google_id' => 'test-google-id',
    'email' => 'test@example.com',
    'access_token' => encrypt('dummy-token'),
    'refresh_token' => encrypt('dummy-refresh'),
    'token_expires_at' => now()->addDays(30),
]);

// 広告アカウントを作成
$adAccount = \App\Models\AdAccount::create([
    'google_account_id' => $googleAccount->id,
    'customer_id' => '123-456-7890',
    'name' => 'テスト広告アカウント',
    'currency_code' => 'JPY',
    'timezone' => 'Asia/Tokyo',
]);

// Analyticsプロパティを作成
$analyticsProperty = \App\Models\AnalyticsProperty::create([
    'google_account_id' => $googleAccount->id,
    'property_id' => '12345678',
    'name' => 'テストAnalyticsプロパティ',
    'timezone' => 'Asia/Tokyo',
]);

// ダミーの広告メトリクスを作成
for ($i = 0; $i < 30; $i++) {
    \App\Models\AdMetricsDaily::create([
        'ad_account_id' => $adAccount->id,
        'date' => now()->subDays($i)->format('Y-m-d'),
        'impressions' => rand(1000, 5000),
        'clicks' => rand(50, 200),
        'cost' => rand(5000, 20000),
        'conversions' => rand(5, 30),
        'conversion_value' => rand(50000, 200000),
    ]);
}

// ダミーのAnalyticsメトリクスを作成
for ($i = 0; $i < 30; $i++) {
    \App\Models\AnalyticsMetricsDaily::create([
        'analytics_property_id' => $analyticsProperty->id,
        'date' => now()->subDays($i)->format('Y-m-d'),
        'sessions' => rand(500, 2000),
        'users' => rand(400, 1500),
        'bounce_rate' => rand(30, 70),
        'conversion_rate' => rand(2, 8),
    ]);
}

echo "✅ テストデータ作成完了！\n";
exit
```

### 2. ブラウザでレポート生成

1. http://localhost/reports にアクセス
2. 「レポート生成」ボタンをクリック
3. 以下を選択：
    - レポートタイプ: 週次
    - 広告アカウント: テスト広告アカウント
    - Analytics プロパティ: テスト Analytics プロパティ
    - 期間: 過去 7 日間
4. 「生成開始」をクリック

### 3. 結果確認

-   レポート一覧に戻る
-   ステータスが「処理中」→「完了」に変わる（数秒～数十秒）
-   レポートをクリックして詳細を確認

## ✅ 成功の確認

以下が表示されれば Gemini 分析が動作しています：

-   📊 総合スコア（1-5）
-   📝 サマリー（AI による分析結果）
-   💡 インサイト一覧
-   ✨ 改善施策一覧

## ⚠️ エラーが出た場合

### 「GEMINI_API_KEY is not set」

```bash
# .envを確認
cat .env | grep GEMINI

# 設定をクリア
./vendor/bin/sail artisan config:clear
```

### 「Queue worker is not running」

```bash
# キューワーカーを起動
./vendor/bin/sail artisan queue:work &
```

### ログを確認

```bash
tail -f storage/logs/laravel.log
```

## 🎯 完了したら

レポートが正常に生成されたら教えてください。
次は Google 連携の設定に進みます！
