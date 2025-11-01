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
        Log::info("GeminiService: Calling API with model {$this->model}");
        try {
            $url = "{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}";

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])
                ->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                    'generationConfig' => array_merge(
                        config('gemini.generation_config', []),
                        $options
                    ),
                ]);

            Log::info("GeminiService: Response status " . $response->status());
            if ($response->successful()) {
                $data = $response->json();

                $parsed = $this->parseResponse($data);
                return [
                    'parsed' => $parsed['parsed'] ?? null,
                    'raw' => $data,
                    'raw_text' => $parsed['raw_text'] ?? null,
                ];
            }

            Log::error('Gemini API error: ' . $response->body());
            return [
                'parsed' => null,
                'raw' => null,
                'raw_text' => null,
            ];
        } catch (\Exception $e) {
            Log::error('Gemini Service error: ' . $e->getMessage());
            Log::error('Gemini Service stack: ' . $e->getTraceAsString());
            return [
                'parsed' => null,
                'raw' => null,
                'raw_text' => null,
            ];
        }
    }

    /**
     * レスポンスをパース
     */
    protected function parseResponse(array $data): array
    {
        if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return ['parsed' => null, 'raw_text' => null];
        }

        $text = $data['candidates'][0]['content']['parts'][0]['text'];

        // JSONを探してパース
        if (preg_match('/\{[\s\S]*\}/', $text, $matches)) {
            try {
                $parsed = json_decode($matches[0], true);
                return ['parsed' => $parsed, 'raw_text' => $text];
            } catch (\Exception $e) {
                Log::error('Failed to parse JSON from Gemini: ' . $e->getMessage());
            }
        }

        // JSON形式でない場合はraw_textのみ返す
        return ['parsed' => null, 'raw_text' => $text];
    }

    /**
     * パフォーマンス分析のプロンプトを生成して実行
     */
    public function analyzePerformance(array $adData, array $analyticsData = [], array $keywordData = []): ?array
    {
        $prompt = $this->buildAnalysisPrompt($adData, $analyticsData, $keywordData);
        $result = $this->generateContent($prompt);
        if (is_array($result)) {
            $result['prompt'] = $prompt;
        }
        return $result;
    }

    /**
     * 分析プロンプトを構築
     */
    protected function buildAnalysisPrompt(array $adData, array $analyticsData = [], array $keywordData = []): string
    {
        $prompt = "あなたは日本語で回答するデジタルマーケティングの上級アナリストです。以下のGoogle広告/Analyticsの集計値を基に、具体的で実行可能な改善提案を出してください。抽象論や一般論は避け、現場で即実施できる手順・指示書レベルの出力を求めます。\n\n";
        $prompt .= "## Google広告データ\n";
        $prompt .= "- 総インプレッション: " . number_format($adData['impressions'] ?? 0) . "\n";
        $prompt .= "- 総クリック数: " . number_format($adData['clicks'] ?? 0) . "\n";
        $prompt .= "- CTR: " . number_format($adData['ctr'] ?? 0, 2) . "%\n";
        $prompt .= "- 総コスト: ¥" . number_format($adData['cost'] ?? 0) . "\n";
        $prompt .= "- 総コンバージョン数: " . number_format($adData['conversions'] ?? 0) . "\n";
        $prompt .= "- CPA: ¥" . number_format($adData['cpa'] ?? 0) . "\n";
        $prompt .= "- ROAS: " . number_format($adData['roas'] ?? 0, 2) . "\n\n";

        // 追加の派生指標
        $cpc = (($adData['clicks'] ?? 0) > 0) ? (($adData['cost'] ?? 0) / ($adData['clicks'] ?? 1)) : 0;
        $prompt .= "- 参考CPC: ¥" . number_format($cpc, 2) . "\n\n";

        if (!empty($keywordData)) {
            $prompt .= "## キーワードサマリ\n";
            if (!empty($keywordData['top_keywords'])) {
                $prompt .= "- クリック上位キーワード:\n";
                foreach (array_slice($keywordData['top_keywords'], 0, 5) as $kw) {
                    $prompt .= sprintf(
                        "  * %s (%s) clicks:%d cvr:%.2f%% cpc:¥%.2f cpa:¥%.2f\n",
                        $kw['keyword'],
                        $kw['match_type'],
                        $kw['clicks'],
                        ($kw['cvr'] * 100),
                        $kw['cpc'],
                        $kw['cpa']
                    );
                }
            }
            if (!empty($keywordData['poor_keywords'])) {
                $prompt .= "- 低CVRキーワード(要改善):\n";
                foreach (array_slice($keywordData['poor_keywords'], 0, 5) as $kw) {
                    $prompt .= sprintf(
                        "  * %s (%s) clicks:%d cvr:%.2f%% cpc:¥%.2f cpa:¥%.2f\n",
                        $kw['keyword'],
                        $kw['match_type'],
                        $kw['clicks'],
                        ($kw['cvr'] * 100),
                        $kw['cpc'],
                        $kw['cpa']
                    );
                }
                $prompt .= "\n";
            }
        }

        if (!empty($analyticsData)) {
            $prompt .= "## Googleアナリティクスデータ\n";
            $prompt .= "- セッション数: " . number_format($analyticsData['sessions'] ?? 0) . "\n";
            $prompt .= "- ユーザー数: " . number_format($analyticsData['users'] ?? 0) . "\n";
            $prompt .= "- 直帰率: " . number_format($analyticsData['bounce_rate'] ?? 0, 2) . "%\n";
            $prompt .= "- コンバージョン率: " . number_format($analyticsData['conversion_rate'] ?? 0, 2) . "%\n\n";
        }

        $prompt .= "要件:\n";
        $prompt .= "- クリック単価(CPC)、CTR、CV、CPAの関係から、どこにボトルネックがあるか診断\n";
        $prompt .= "- 検索キーワード観点での具体提案（例: 現状キーワードの見直し、除外KW追加、新規KWの追加案3つ以上、ネガティブKW3つ以上）\n";
        $prompt .= "- 広告文の改善例（見出し/説明文の候補を各2-3案。日本語で）\n";
        $prompt .= "- 入札/予算配分/入札戦略の具体調整案\n";
        $prompt .= "- 実施手順をステップ形式で具体的に（人がそのまま実行できるレベル）\n";

        $prompt .= "以下のJSON形式で厳密に回答してください（余計なテキストは出力しない）:\n";
        $prompt .= "{\n";
        $prompt .= '  "overall_performance": { "score": 1-5, "summary": "要点の日本語サマリー" },\n';
        $prompt .= '  "insights": [\n';
        $prompt .= '    { "category": "performance|budget|targeting|creative|conversion", "priority": "high|medium|low", "title": "所見タイトル", "description": "詳細", "impact_score": 1-10, "confidence_score": 0-1 }\n';
        $prompt .= '  ],\n';
        $prompt .= '  "recommendations": [\n';
        $prompt .= '    {\n';
        $prompt .= '      "insight_index": 0,\n';
        $prompt .= '      "title": "具体施策タイトル",\n';
        $prompt .= '      "description": "なぜ有効か/期待効果",\n';
        $prompt .= '      "action_type": "budget_adjustment|keyword_addition|ad_copy_change|bid_adjustment",\n';
        $prompt .= '      "estimated_impact": "期待できる効果(日本語)",\n';
        $prompt .= '      "difficulty": "easy|medium|hard",\n';
        $prompt .= '      "specific_actions": ["手順1", "手順2", "手順3"],\n';
        $prompt .= '      "keyword_suggestions": {\n';
        $prompt .= '        "add": ["新規追加すべきKW(完全一致/フレーズなどルールも)"],\n';
        $prompt .= '        "negative": ["追加すべき除外KW"],\n';
        $prompt .= '        "ad_copy_examples": [{"headline": ["見出し案1","見出し案2"], "description": ["説明文案1","説明文案2"]}]\n';
        $prompt .= '      }\n';
        $prompt .= '    }\n';
        $prompt .= '  ]\n';
        $prompt .= "}\n";

        return $prompt;
    }
}
