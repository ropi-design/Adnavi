<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected string $apiKey;
    protected string $model;
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';

    public function __construct()
    {
        $this->apiKey = config('gemini.api_key');
        $this->model = config('gemini.model');
    }

    /**
     * Gemini APIでコンテンツを生成
     */
    public function generateContent(string $prompt, array $options = []): ?array
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])
                ->post("{$this->baseUrl}/models/{$this->model}:generateContent", [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                    'generationConfig' => array_merge(
                        config('gemini.generation_config'),
                        $options
                    ),
                ], [
                    'key' => $this->apiKey,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                return $this->parseResponse($data);
            }

            Log::error('Gemini API error: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Gemini Service error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * レスポンスをパース
     */
    protected function parseResponse(array $data): ?array
    {
        if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return null;
        }

        $text = $data['candidates'][0]['content']['parts'][0]['text'];

        // JSONを探してパース
        if (preg_match('/\{[\s\S]*\}/', $text, $matches)) {
            try {
                return json_decode($matches[0], true);
            } catch (\Exception $e) {
                Log::error('Failed to parse JSON from Gemini: ' . $e->getMessage());
            }
        }

        // JSON形式でない場合はそのまま返す
        return ['raw_text' => $text];
    }

    /**
     * パフォーマンス分析のプロンプトを生成して実行
     */
    public function analyzePerformance(array $adData, array $analyticsData = []): ?array
    {
        $prompt = $this->buildAnalysisPrompt($adData, $analyticsData);

        return $this->generateContent($prompt);
    }

    /**
     * 分析プロンプトを構築
     */
    protected function buildAnalysisPrompt(array $adData, array $analyticsData = []): string
    {
        $prompt = "あなたはデジタルマーケティングの専門家です。以下のGoogle広告とGoogleアナリティクスのデータを分析し、パフォーマンスの評価と改善提案を行ってください。\n\n";
        $prompt .= "## Google広告データ\n";
        $prompt .= "- 総インプレッション: " . number_format($adData['impressions'] ?? 0) . "\n";
        $prompt .= "- 総クリック数: " . number_format($adData['clicks'] ?? 0) . "\n";
        $prompt .= "- CTR: " . number_format($adData['ctr'] ?? 0, 2) . "%\n";
        $prompt .= "- 総コスト: ¥" . number_format($adData['cost'] ?? 0) . "\n";
        $prompt .= "- 総コンバージョン数: " . number_format($adData['conversions'] ?? 0) . "\n";
        $prompt .= "- CPA: ¥" . number_format($adData['cpa'] ?? 0) . "\n";
        $prompt .= "- ROAS: " . number_format($adData['roas'] ?? 0, 2) . "\n\n";

        if (!empty($analyticsData)) {
            $prompt .= "## Googleアナリティクスデータ\n";
            $prompt .= "- セッション数: " . number_format($analyticsData['sessions'] ?? 0) . "\n";
            $prompt .= "- ユーザー数: " . number_format($analyticsData['users'] ?? 0) . "\n";
            $prompt .= "- 直帰率: " . number_format($analyticsData['bounce_rate'] ?? 0, 2) . "%\n";
            $prompt .= "- コンバージョン率: " . number_format($analyticsData['conversion_rate'] ?? 0, 2) . "%\n\n";
        }

        $prompt .= "以下のJSON形式で回答してください:\n";
        $prompt .= "{\n";
        $prompt .= '  "overall_performance": { "score": 1-5, "summary": "評価..." },\n';
        $prompt .= '  "insights": [ { "category": "performance|budget|targeting|creative|conversion", "priority": "high|medium|low", "title": "...", "description": "...", "impact_score": 1-10, "confidence_score": 0-1 } ],\n';
        $prompt .= '  "recommendations": [ { "insight_index": 0, "title": "...", "description": "...", "action_type": "budget_adjustment|keyword_addition|ad_copy_change", "estimated_impact": "...", "difficulty": "easy|medium|hard", "specific_actions": [...] } ]\n';
        $prompt .= "}\n";

        return $prompt;
    }
}
