<?php

namespace App\Services\Google;

use App\Models\AnalyticsProperty;
use App\Models\GoogleAccount;
use Google\Client;
use Google\Service\AnalyticsData;
use Google\Service\AnalyticsReporting;
use Illuminate\Support\Facades\Log;

class GoogleAnalyticsService
{
    protected Client $client;
    protected ?GoogleAccount $googleAccount = null;

    /**
     * Google Analytics クライアントの初期化
     */
    public function initialize(?GoogleAccount $googleAccount = null): void
    {
        $this->googleAccount = $googleAccount;

        if (!$googleAccount) {
            throw new \Exception('Google アカウントが指定されていません');
        }

        if (!$googleAccount->isTokenValid()) {
            throw new \Exception('Google アカウントのトークンが無効です');
        }

        $this->client = $this->createClient($googleAccount);
    }

    /**
     * Google Clientを作成
     */
    protected function createClient(GoogleAccount $googleAccount): Client
    {
        try {
            $client = new Client();
            $client->setClientId(config('services.google.client_id'));
            $client->setClientSecret(config('services.google.client_secret'));

            // アクセストークンを復号化
            $decryptedToken = decrypt($googleAccount->access_token);

            // トークンが文字列の場合はJSONとして解析を試みる
            if (is_string($decryptedToken)) {
                $tokenData = json_decode($decryptedToken, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($tokenData)) {
                    $client->setAccessToken($tokenData);
                } else {
                    // JSONでない場合は直接設定
                    $client->setAccessToken([
                        'access_token' => $decryptedToken,
                        'expires_in' => $googleAccount->token_expires_at ?
                            now()->diffInSeconds($googleAccount->token_expires_at) : 3600,
                    ]);
                }
            } else {
                $client->setAccessToken($decryptedToken);
            }

            $client->setScopes([
                'https://www.googleapis.com/auth/analytics.readonly',
                'https://www.googleapis.com/auth/analytics',
            ]);

            // トークンをリフレッシュする必要がある場合
            if ($client->isAccessTokenExpired()) {
                if ($googleAccount->refresh_token) {
                    $refreshToken = decrypt($googleAccount->refresh_token);
                    $client->refreshToken($refreshToken);
                    $newToken = $client->getAccessToken();

                    // 新しいトークンを保存
                    if (is_array($newToken)) {
                        $googleAccount->update([
                            'access_token' => encrypt(json_encode($newToken)),
                            'token_expires_at' => isset($newToken['expires_in']) ?
                                now()->addSeconds($newToken['expires_in']) : now()->addHour(),
                        ]);
                    }
                } else {
                    throw new \Exception('リフレッシュトークンが利用できません');
                }
            }

            return $client;
        } catch (\Exception $e) {
            Log::error('Failed to create Google Client', [
                'error' => $e->getMessage(),
                'google_account_id' => $googleAccount->id,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * アクセス可能なAnalyticsプロパティを取得（GA4）
     * 
     * Google Analytics Admin APIを使用してプロパティ一覧を取得します
     * 注意: google/apiclient-servicesパッケージが必要な場合があります
     */
    public function getProperties(): array
    {
        try {
            // Google Analytics Admin APIを使用
            // 注意: クラス名はパッケージによって異なる場合があります
            $serviceClass = 'Google\Service\AnalyticsReporting';

            // まずはREST APIを直接使用する方法を試みる
            $properties = $this->getPropertiesViaRestApi();

            return $properties;
        } catch (\Exception $e) {
            Log::error('Failed to get Analytics properties: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \Exception('Analyticsプロパティの取得に失敗しました: ' . $e->getMessage());
        }
    }

    /**
     * REST APIを使用してプロパティを取得
     */
    protected function getPropertiesViaRestApi(): array
    {
        $accessToken = $this->client->getAccessToken();

        // トークンを取得
        if (is_array($accessToken)) {
            $token = $accessToken['access_token'] ?? null;
        } else {
            $token = $accessToken;
        }

        if (!$token) {
            throw new \Exception('アクセストークンが取得できませんでした');
        }

        // Google Analytics Admin API v1betaを使用（v1alphaは非推奨の可能性あり）
        $url = 'https://analyticsadmin.googleapis.com/v1beta/accounts';

        try {
            $ch = curl_init();
            if ($ch === false) {
                throw new \Exception('cURLの初期化に失敗しました');
            }

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

            $response = curl_exec($ch);
            $curlError = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($curlError) {
                throw new \Exception('cURLエラー: ' . $curlError);
            }

            if ($httpCode !== 200) {
                $error = json_decode($response, true);
                $errorMessage = $error['error']['message'] ?? 'Unknown error';
                Log::warning('Failed to get Analytics accounts', [
                    'http_code' => $httpCode,
                    'error' => $errorMessage,
                    'response' => $response,
                ]);
                throw new \Exception('Failed to get accounts: ' . $errorMessage);
            }
        } catch (\Exception $e) {
            Log::error('Error in getPropertiesViaRestApi', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }

        $accountsData = json_decode($response, true);
        $properties = [];

        if (!isset($accountsData['accounts'])) {
            return $properties;
        }

        foreach ($accountsData['accounts'] as $account) {
            $accountName = $account['name'];

            // 各アカウントのプロパティを取得
            // Google Analytics Admin API v1betaを使用
            // 注意: プロパティはアカウントに直接紐づいているのではなく、別のエンドポイントから取得する必要がある
            // アカウントIDから数値部分を抽出
            $accountId = str_replace('accounts/', '', $accountName);

            // プロパティ一覧を取得（アカウントに紐づくプロパティを検索）
            $propertiesUrl = 'https://analyticsadmin.googleapis.com/v1beta/properties?filter=parent:accounts/' . $accountId;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $propertiesUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ]);

            $propertiesResponse = curl_exec($ch);
            $propertiesHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($propertiesHttpCode === 200) {
                $propertiesData = json_decode($propertiesResponse, true);

                // APIレスポンスの形式を確認
                if (isset($propertiesData['properties']) && is_array($propertiesData['properties'])) {
                    foreach ($propertiesData['properties'] as $property) {
                        $propertyName = $property['name'] ?? '';
                        $propertyId = str_replace('properties/', '', $propertyName);

                        $properties[] = [
                            'property_id' => $propertyId,
                            'property_name' => $property['displayName'] ?? $propertyId,
                            'timezone' => $property['timeZone'] ?? 'Asia/Tokyo',
                            'account_name' => $account['displayName'] ?? $accountName,
                        ];
                    }
                } else {
                    Log::warning("No properties found in response for account {$accountName}", [
                        'response_keys' => array_keys($propertiesData ?? []),
                        'response_sample' => substr($propertiesResponse ?? '', 0, 500),
                    ]);
                }
            } else {
                Log::warning("Failed to get properties for account {$accountName}", [
                    'http_code' => $propertiesHttpCode,
                    'url' => $propertiesUrl,
                    'response' => substr($propertiesResponse ?? '', 0, 500),
                ]);
            }
        }

        return $properties;
    }

    /**
     * プロパティをデータベースに保存
     */
    public function syncPropertiesToDatabase(): int
    {
        if (!$this->googleAccount) {
            throw new \Exception('Googleアカウントが初期化されていません');
        }

        try {
            $properties = $this->getProperties();
            $savedCount = 0;

            foreach ($properties as $propertyData) {
                // property_idから数値部分のみを抽出（例: "properties/123456789" → "123456789"）
                $propertyId = str_replace('properties/', '', $propertyData['property_id']);

                $analyticsProperty = AnalyticsProperty::updateOrCreate(
                    [
                        'property_id' => $propertyId,
                        'google_account_id' => $this->googleAccount->id,
                    ],
                    [
                        'user_id' => $this->googleAccount->user_id,
                        'property_name' => $propertyData['property_name'],
                        'timezone' => $propertyData['timezone'],
                        'is_active' => true,
                        'last_synced_at' => now(),
                    ]
                );

                if ($analyticsProperty->wasRecentlyCreated || $analyticsProperty->wasChanged()) {
                    $savedCount++;
                }
            }

            return $savedCount;
        } catch (\Exception $e) {
            Log::error('Failed to sync Analytics properties to database: ' . $e->getMessage(), [
                'google_account_id' => $this->googleAccount->id,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * メトリクスを取得（将来の実装用）
     */
    public function getMetrics(string $propertyId, string $startDate, string $endDate): array
    {
        // TODO: Google Analytics Data APIを使用してメトリクスを取得
        return [];
    }
}
