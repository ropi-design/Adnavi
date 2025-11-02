<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected string $apiKey;
    protected string $model;
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';
    protected int $timeoutMs;

    public function __construct()
    {
        $this->apiKey = config('gemini.api_key');
        $this->model = config('gemini.model', 'gemini-2.5-flash');
        $this->timeoutMs = (int)config('gemini.request_timeout_ms', 30000);

        // APIキーの検証
        if (empty($this->apiKey)) {
            Log::error('GEMINI_API_KEY is not configured in .env file');
        }
        if (empty($this->model)) {
            Log::error('GEMINI_MODEL is not configured in .env file');
        }
    }

    /**
     * Gemini APIでコンテンツを生成（マルチモーダル対応）
     * 
     * @param string|array $prompt テキストプロンプトまたはマルチモーダルコンテンツ
     * @param array $options 生成オプション
     * @param string|null $imageUrl 画像URL（オプション）
     * @param string|null $systemInstruction システム指示（オプション）
     * @return array|null
     */
    public function generateContent(string|array $prompt, array $options = [], ?string $imageUrl = null, ?string $systemInstruction = null): ?array
    {
        Log::info("GeminiService: Calling API with model {$this->model}");
        try {
            $url = "{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}";

            // コンテンツの構築
            $parts = [];

            // システム指示がある場合
            if (!empty($systemInstruction)) {
                $parts[] = ['text' => $systemInstruction];
            }

            // テキストプロンプト
            if (is_string($prompt)) {
                $parts[] = ['text' => $prompt];
            } elseif (is_array($prompt)) {
                $parts = array_merge($parts, $prompt);
            }

            // 画像URLがある場合
            if (!empty($imageUrl)) {
                $parts[] = [
                    'inline_data' => [
                        'mime_type' => $this->detectImageMimeType($imageUrl),
                        'data' => $this->fetchImageAsBase64($imageUrl),
                    ],
                ];
            }

            $payload = [
                'contents' => [
                    [
                        'parts' => $parts,
                    ],
                ],
                'generationConfig' => array_merge(
                    config('gemini.generation_config', []),
                    $options
                ),
            ];

            // システム指示がある場合は別フィールドに設定（Gemini 2.5対応）
            if (!empty($systemInstruction)) {
                $payload['systemInstruction'] = [
                    'parts' => [
                        ['text' => $systemInstruction],
                    ],
                ];
            }

            $response = Http::timeout($this->timeoutMs / 1000)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($url, $payload);

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

            // エラーレスポンスの詳細をログ
            $errorBody = $response->body();
            $status = $response->status();
            Log::error("Gemini API error (status: {$status}): {$errorBody}");

            return [
                'parsed' => null,
                'raw' => null,
                'raw_text' => null,
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Gemini Service timeout: ' . $e->getMessage());
            return [
                'parsed' => null,
                'raw' => null,
                'raw_text' => null,
                'error' => 'Request timeout',
            ];
        } catch (\Exception $e) {
            Log::error('Gemini Service error: ' . $e->getMessage());
            Log::error('Gemini Service stack: ' . $e->getTraceAsString());
            return [
                'parsed' => null,
                'raw' => null,
                'raw_text' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * 画像URLからBase64データを取得
     */
    protected function fetchImageAsBase64(string $imageUrl): string
    {
        try {
            $imageData = Http::timeout(10)->get($imageUrl)->body();
            return base64_encode($imageData);
        } catch (\Exception $e) {
            Log::error("Failed to fetch image from URL: {$imageUrl} - " . $e->getMessage());
            throw new \RuntimeException("Failed to fetch image: " . $e->getMessage());
        }
    }

    /**
     * 画像URLからMIMEタイプを検出
     */
    protected function detectImageMimeType(string $imageUrl): string
    {
        $extension = strtolower(pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION));

        return match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'image/jpeg', // デフォルト
        };
    }

    /**
     * レスポンスをパース
     */
    protected function parseResponse(array $data): array
    {
        if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            Log::warning('Gemini response missing text content');
            return ['parsed' => null, 'raw_text' => null];
        }

        $text = $data['candidates'][0]['content']['parts'][0]['text'];

        // テキストから先頭のマークダウンコードブロックや余計なテキストを除去
        $text = preg_replace('/^[\s\S]*?```(?:json)?\s*/i', '', $text);
        $text = preg_replace('/```\s*[\s\S]*$/i', '', $text);
        $text = trim($text);

        // ネストされたJSONを正確にマッチ
        $depth = 0;
        $startPos = strpos($text, '{');

        if ($startPos === false) {
            Log::warning('No opening brace found in response');
            return ['parsed' => null, 'raw_text' => $text];
        }

        $jsonEndPos = $startPos;
        for ($i = $startPos; $i < strlen($text); $i++) {
            $char = $text[$i];
            if ($char === '{') {
                $depth++;
            } elseif ($char === '}') {
                $depth--;
                if ($depth === 0) {
                    $jsonEndPos = $i + 1;
                    break;
                }
            }
        }

        if ($depth !== 0) {
            Log::warning('Unbalanced braces in JSON response');
            return ['parsed' => null, 'raw_text' => $text];
        }

        $jsonText = substr($text, $startPos, $jsonEndPos - $startPos);

        try {
            $parsed = json_decode($jsonText, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
                return ['parsed' => $parsed, 'raw_text' => $text];
            }
            Log::error('JSON decode error: ' . json_last_error_msg());
        } catch (\Exception $e) {
            Log::error('Failed to parse JSON from Gemini: ' . $e->getMessage());
        }

        // JSON形式でない場合はraw_textのみ返す
        Log::warning('No valid JSON found in Gemini response');
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
            return $result;
        }
        // API呼び出し失敗時はpromptだけ返す
        return [
            'parsed' => null,
            'raw' => null,
            'raw_text' => null,
            'prompt' => $prompt,
        ];
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
        $prompt .= "- データを深く分析し、パターン、傾向、異常値を特定してください\n";
        $prompt .= "- 各インサイトには具体的な数値、比較、パーセンテージを含めてください（例: 「CTRが2.1%と平均2.5%を下回り、改善余地があります」）\n";
        $prompt .= "- 根本原因分析を行い、なぜ問題が発生しているのかを説明してください\n";
        $prompt .= "- クリック単価(CPC)、CTR、CV、CPAの関係から、どこにボトルネックがあるか診断\n";
        $prompt .= "- 改善の緊急度と重要度を明確に示してください\n";
        $prompt .= "- 可能な限り多くのインサイト（最低5つ以上）を生成してください\n";
        $prompt .= "- 各インサイトには以下の情報を含めてください:\n";
        $prompt .= "  * 現在の値とベンチマーク値の比較\n";
        $prompt .= "  * 影響を受ける指標（CTR、CVR、CPAなど）\n";
        $prompt .= "  * 改善が見込まれる具体的な数値目標\n";
        $prompt .= "  * データポイント（分析に使用した具体的な数値）\n";
        $prompt .= "- 検索キーワード観点での具体提案（例: 現状キーワードの見直し、除外KW追加、新規KWの追加案3つ以上、ネガティブKW3つ以上）\n";
        $prompt .= "- 広告文の改善例（見出し/説明文の候補を各2-3案。日本語で）\n";
        $prompt .= "- 入札/予算配分/入札戦略の具体調整案\n";
        $prompt .= "- 実施手順をステップ形式で具体的に（人がそのまま実行できるレベル）\n";

        $prompt .= "以下のJSON形式で厳密に回答してください（余計なテキストは出力しない）:\n";
        $prompt .= "{\n";
        $prompt .= '  "overall_performance": { "score": 1-5, "summary": "要点の日本語サマリー（200文字以上で詳細に）" },\n';
        $prompt .= '  "insights": [\n';
        $prompt .= '    { "category": "performance|budget|targeting|creative|conversion", "priority": "high|medium|low", "title": "詳細な所見タイトル（具体的な数値を含む）", "description": "詳細な説明（現在の値、目標値、影響範囲、根本原因を含む300文字以上）", "impact_score": 1-10, "confidence_score": 0-1, "data_points": { "current_value": 数値, "target_value": 数値, "benchmark": 数値, "affected_metrics": ["指標1", "指標2"] } }\n';
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

    /**
     * 改善施策について質問する（Gemini 2.5 Flash対応）
     */
    public function askAboutRecommendation(string $question, array $recommendationData, ?string $imageUrl = null): ?string
    {
        $prompt = $this->buildRecommendationQuestionPrompt($question, $recommendationData);

        Log::info("Asking question about recommendation", [
            'question' => $question,
            'recommendation_title' => $recommendationData['title'] ?? 'N/A',
            'has_image' => !empty($imageUrl),
        ]);

        try {
            $result = $this->generateContent($prompt, [], $imageUrl);

            if ($result && isset($result['raw_text'])) {
                Log::info("Question answered successfully");
                return trim($result['raw_text']);
            }

            if (isset($result['error'])) {
                Log::error("Gemini API question failed: " . $result['error']);
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Gemini question error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * インサイトについて質問する（Gemini 2.5 Flash対応）
     */
    public function askAboutInsight(string $question, array $insightData, ?string $imageUrl = null): ?string
    {
        $prompt = $this->buildInsightQuestionPrompt($question, $insightData);

        Log::info("Asking question about insight", [
            'question' => $question,
            'insight_title' => $insightData['title'] ?? 'N/A',
            'has_image' => !empty($imageUrl),
        ]);

        try {
            $result = $this->generateContent($prompt, [], $imageUrl);

            if ($result && isset($result['raw_text'])) {
                Log::info("Question answered successfully");
                return trim($result['raw_text']);
            }

            if (isset($result['error'])) {
                Log::error("Gemini API question failed: " . $result['error']);
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Gemini question error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * インサイト質問用のプロンプトを構築
     */
    protected function buildInsightQuestionPrompt(string $question, array $insightData): string
    {
        $prompt = "あなたはデジタルマーケティングの専門家です。以下のインサイトについて質問に答えてください。\n\n";

        $prompt .= "## インサイトの詳細\n";
        $prompt .= "タイトル: {$insightData['title']}\n";
        $prompt .= "説明: {$insightData['description']}\n";

        if (!empty($insightData['category'])) {
            $categoryLabel = match ($insightData['category']) {
                'performance' => 'パフォーマンス',
                'budget' => '予算',
                'targeting' => 'ターゲティング',
                'creative' => 'クリエイティブ',
                'conversion' => 'コンバージョン',
                default => $insightData['category'],
            };
            $prompt .= "カテゴリ: {$categoryLabel}\n";
        }

        if (!empty($insightData['priority'])) {
            $priorityLabel = match ($insightData['priority']) {
                'high' => '高',
                'medium' => '中',
                'low' => '低',
                default => $insightData['priority'],
            };
            $prompt .= "優先度: {$priorityLabel}\n";
        }

        if (!empty($insightData['impact_score'])) {
            $prompt .= "インパクトスコア: {$insightData['impact_score']}/10\n";
        }

        if (!empty($insightData['confidence_score'])) {
            $prompt .= "信頼度: " . number_format($insightData['confidence_score'] * 100, 2) . "%\n";
        }

        if (!empty($insightData['data_points'])) {
            $prompt .= "データポイント:\n";
            $dataPoints = is_array($insightData['data_points']) ? $insightData['data_points'] : json_decode($insightData['data_points'], true);
            if ($dataPoints) {
                foreach ($dataPoints as $key => $value) {
                    if (is_array($value)) {
                        $prompt .= "  {$key}: " . implode(', ', $value) . "\n";
                    } else {
                        $prompt .= "  {$key}: {$value}\n";
                    }
                }
            }
        }

        $prompt .= "\n## 質問\n";
        $prompt .= "{$question}\n\n";
        $prompt .= "上記のインサイトに関する質問に対して、具体的で実践的な回答を日本語で提供してください。";

        return $prompt;
    }

    /**
     * 改善施策質問用のプロンプトを構築
     */
    protected function buildRecommendationQuestionPrompt(string $question, array $recommendationData): string
    {
        $prompt = "あなたはデジタルマーケティングの専門家です。以下の改善施策について質問に答えてください。\n\n";

        $prompt .= "## 改善施策の詳細\n";
        $prompt .= "タイトル: {$recommendationData['title']}\n";
        $prompt .= "説明: {$recommendationData['description']}\n";

        if (!empty($recommendationData['estimated_impact'])) {
            $prompt .= "推定効果: {$recommendationData['estimated_impact']}\n";
        }

        if (!empty($recommendationData['implementation_difficulty'])) {
            $difficultyLabel = match ($recommendationData['implementation_difficulty']) {
                'easy' => '簡単',
                'medium' => '普通',
                'hard' => '難しい',
                default => $recommendationData['implementation_difficulty'],
            };
            $prompt .= "実施難易度: {$difficultyLabel}\n";
        }

        if (!empty($recommendationData['specific_actions']) && is_array($recommendationData['specific_actions'])) {
            $prompt .= "実施手順:\n";
            foreach ($recommendationData['specific_actions'] as $index => $action) {
                $prompt .= ($index + 1) . ". {$action}\n";
            }
        }

        $prompt .= "\n## 質問\n";
        $prompt .= "{$question}\n\n";
        $prompt .= "上記の改善施策に関する質問に対して、具体的で実践的な回答を日本語で提供してください。";

        return $prompt;
    }
}
