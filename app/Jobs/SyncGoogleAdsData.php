<?php

namespace App\Jobs;

use App\Models\AdAccount;
use App\Services\Google\GoogleAdsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncGoogleAdsData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public AdAccount $adAccount,
        public string $startDate,
        public string $endDate,
    ) {}

    /**
     * ジョブの実行
     */
    public function handle(GoogleAdsService $adsService): void
    {
        Log::info("Starting Google Ads data sync for account: {$this->adAccount->account_name}");

        try {
            // TODO: Google Ads APIからデータを取得して保存
            // $metrics = $adsService->getMetrics($this->adAccount, $this->startDate, $this->endDate);
            // $this->saveMetrics($metrics);

            Log::info("Google Ads data sync completed for account: {$this->adAccount->account_name}");

            // 最後の同期日時を更新
            $this->adAccount->update([
                'last_synced_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error("Google Ads data sync failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * ジョブが失敗した場合の処理
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Google Ads data sync job failed for account: {$this->adAccount->account_name}", [
            'error' => $exception->getMessage(),
        ]);
    }
}
