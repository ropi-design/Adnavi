<?php

namespace App\Services\Google;

use App\Models\GoogleAccount;
use Google\Ads\GoogleAds\Lib\OAuth2TokenBuilder;
use Google\Ads\GoogleAds\Lib\V16\GoogleAdsClient;
use Google\Ads\GoogleAds\Lib\V16\GoogleAdsClientBuilder;
use Illuminate\Support\Facades\Log;

class GoogleAdsService
{
    protected GoogleAdsClient $client;
    protected ?GoogleAccount $googleAccount = null;

    /**
     * Google Ads クライアントの初期化
     */
    public function initialize(?GoogleAccount $googleAccount = null): void
    {
        $this->googleAccount = $googleAccount;

        if ($googleAccount && $googleAccount->isTokenValid()) {
            $this->client = $this->createClientWithToken($googleAccount);
        } else {
            throw new \Exception('Google アカウントが連携されていないか、トークンが無効です');
        }
    }

    /**
     * トークンを使ってクライアントを作成
     */
    protected function createClientWithToken(GoogleAccount $googleAccount): GoogleAdsClient
    {
        $builder = new GoogleAdsClientBuilder();
        $builder->withDeveloperToken(config('google-ads.developer_token'))
            ->withOAuth2Credential(
                OAuth2TokenBuilder::fromBuilder()
                    ->withClientId(config('google-ads.client_id'))
                    ->withClientSecret(config('google-ads.client_secret'))
                    ->withRefreshToken(decrypt($googleAccount->refresh_token))
                    ->build()
            )
            ->withLoginCustomerId(config('google-ads.login_customer_id'));

        return $builder->build();
    }

    /**
     * アクセス可能な顧客リストを取得
     */
    public function getAccessibleCustomers(): array
    {
        try {
            $customerServiceClient = $this->client->getCustomerServiceClient();
            $response = $customerServiceClient->listAccessibleCustomers();

            return $response->getResourceNames();
        } catch (\Exception $e) {
            Log::error('Failed to get accessible customers: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * キャンペーン一覧を取得
     */
    public function getCampaigns(string $customerId): array
    {
        try {
            $googleAdsServiceClient = $this->client->getGoogleAdsServiceClient();

            $query = "SELECT campaign.id, campaign.name, campaign.status, 
                             campaign.advertising_channel_type, campaign_budget.amount_micros
                      FROM campaign
                      WHERE campaign.status != 'REMOVED'
                      ORDER BY campaign.id";

            $response = $googleAdsServiceClient->search($customerId, $query);

            $campaigns = [];
            foreach ($response->getResults() as $row) {
                $campaigns[] = [
                    'id' => $row->getCampaign()->getId(),
                    'name' => $row->getCampaign()->getName(),
                    'status' => $row->getCampaign()->getStatus(),
                    'type' => $row->getCampaign()->getAdvertisingChannelType(),
                    'budget' => $row->getCampaignBudget()->getAmountMicros() / 1000000, // マイクロ通貨単位を変換
                ];
            }

            return $campaigns;
        } catch (\Exception $e) {
            Log::error("Failed to get campaigns for customer {$customerId}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * メトリクスを取得
     */
    public function getMetrics(string $customerId, string $startDate, string $endDate): array
    {
        try {
            $googleAdsServiceClient = $this->client->getGoogleAdsServiceClient();

            $query = "SELECT segments.date, 
                             metrics.impressions, metrics.clicks, metrics.cost_micros,
                             metrics.conversions, metrics.conversion_value,
                             metrics.ctr, metrics.average_cpc, metrics.cost_per_conversion, metrics.value_per_conversion
                      FROM campaign WHERE segments.date BETWEEN '{$startDate}' AND '{$endDate}'
                      ORDER BY segments.date DESC";

            $response = $googleAdsServiceClient->search($customerId, $query);

            $metrics = [];
            foreach ($response->getResults() as $row) {
                $metrics[] = [
                    'date' => $row->getSegments()->getDate(),
                    'impressions' => $row->getMetrics()->getImpressions(),
                    'clicks' => $row->getMetrics()->getClicks(),
                    'cost' => $row->getMetrics()->getCostMicros() / 1000000,
                    'conversions' => $row->getMetrics()->getConversions(),
                    'conversion_value' => $row->getMetrics()->getConversionValue(),
                    'ctr' => $row->getMetrics()->getCtr(),
                    'cpc' => $row->getMetrics()->getAverageCpc() / 1000000,
                    'cpa' => $row->getMetrics()->getCostPerConversion() / 1000000,
                    'value_per_conversion' => $row->getMetrics()->getValuePerConversion(),
                ];
            }

            return $metrics;
        } catch (\Exception $e) {
            Log::error("Failed to get metrics for customer {$customerId}: " . $e->getMessage());
            throw $e;
        }
    }
}
