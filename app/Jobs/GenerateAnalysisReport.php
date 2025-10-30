<?php

namespace App\Jobs;

use App\Models\AnalysisReport;
use App\Services\AI\GeminiService;
use App\Services\Analysis\DataAggregator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateAnalysisReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $reportId,
    ) {}

    /**
     * ジョブの実行
     */
    public function handle(GeminiService $geminiService): void
    {
        $report = AnalysisReport::findOrFail($this->reportId);

        Log::info("Starting AI analysis for report: {$report->id}");

        try {
            // ステータスを更新
            $report->update(['status' => 'processing']);

            // DataAggregatorでデータを集約
            $aggregator = app(DataAggregator::class);
            $data = $aggregator->aggregate($report);

            // Geminiで分析（生データと解析済みの両方を受け取る）
            $ai = $geminiService->analyzePerformance(
                $data['ad_data'],
                $data['analytics_data'],
                $data['keyword_data'] ?? []
            );

            $rawText = $ai['raw_text'] ?? null;
            $parsed = $ai['parsed'] ?? null;
            $prompt = $ai['prompt'] ?? null;

            // 解析失敗時は安全なフォールバックを構築
            if (!$parsed) {
                $parsed = $this->buildFallbackResult($data['ad_data'], $data['analytics_data'], $rawText);
            }

            // 値を保存前に正規化
            $analysisResult = $this->sanitizeAnalysisResult($parsed);

            // 結果を保存
            $this->saveResults($report, $analysisResult);

            // ステータスを完了に更新（rawも保存）
            $report->update([
                'status' => 'completed',
                'analysis_result' => $analysisResult,
                'raw_data' => [
                    'prompt' => $prompt,
                    'raw_text' => $rawText,
                ],
            ]);

            Log::info("AI analysis completed for report: {$report->id}");
        } catch (\Exception $e) {
            $report->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error("AI analysis failed for report: {$report->id}", [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * 分析結果を保存
     */
    protected function saveResults(AnalysisReport $report, ?array $result): void
    {
        if (!$result) {
            return;
        }

        // Insightを作成
        $insights = [];
        if (isset($result['insights'])) {
            foreach ($result['insights'] as $index => $insightData) {
                $insight = $this->createInsight($report, $insightData);
                $insights[$index] = $insight;
            }
        }

        // Recommendationを作成（Insightに紐付け）
        if (isset($result['recommendations'])) {
            foreach ($result['recommendations'] as $recData) {
                $insightIndex = $recData['insight_index'] ?? 0;
                $insight = $insights[$insightIndex] ?? $insights[0] ?? null;

                if ($insight) {
                    $this->createRecommendation($insight, $recData);
                }
            }
        }

        // 結果を保存
        $report->update([
            'overall_score' => $result['overall_performance']['score'] ?? null,
            'summary' => $result['overall_performance']['summary'] ?? null,
        ]);
    }

    /**
     * Insightレコードを作成
     */
    protected function createInsight(AnalysisReport $report, array $data)
    {
        return $report->insights()->create([
            'category' => $data['category'] ?? 'performance',
            'priority' => $data['priority'] ?? 'medium',
            'title' => $data['title'] ?? '',
            'description' => $data['description'] ?? '',
            'impact_score' => $data['impact_score'] ?? 5,
            'confidence_score' => $data['confidence_score'] ?? 0.7,
        ]);
    }

    /**
     * Recommendationレコードを作成
     */
    protected function createRecommendation($insight, array $data): void
    {
        $insight->recommendations()->create([
            'analysis_report_id' => $insight->analysis_report_id ?? $insight->analysisReport->id ?? null,
            'title' => $data['title'] ?? '',
            'description' => $data['description'] ?? '',
            'action_type' => $this->normalizeActionType($data['action_type'] ?? 'other'),
            'estimated_impact' => $data['estimated_impact'] ?? '',
            'implementation_difficulty' => $this->normalizeDifficulty($data['difficulty'] ?? 'medium'),
            'specific_actions' => $data['specific_actions'] ?? [],
            'status' => 'pending',
        ]);
    }

    /**
     * AI出力の正規化（Enum/数値の範囲・型を安全化）
     */
    protected function sanitizeAnalysisResult(array $result): array
    {
        // overall_performance
        $score = (int)($result['overall_performance']['score'] ?? 0);
        $score = max(1, min(5, $score));
        $summary = (string)($result['overall_performance']['summary'] ?? '');

        $sanitizedInsights = [];
        if (!empty($result['insights']) && is_array($result['insights'])) {
            foreach ($result['insights'] as $insight) {
                $sanitizedInsights[] = [
                    'category' => $this->normalizeCategory($insight['category'] ?? 'performance'),
                    'priority' => $this->normalizePriority($insight['priority'] ?? 'medium'),
                    'title' => (string)($insight['title'] ?? ''),
                    'description' => (string)($insight['description'] ?? ''),
                    'impact_score' => $this->clampInt($insight['impact_score'] ?? 5, 1, 10),
                    'confidence_score' => $this->clampFloat($insight['confidence_score'] ?? 0.7, 0.0, 1.0),
                    'data_points' => isset($insight['data_points']) && is_array($insight['data_points']) ? $insight['data_points'] : null,
                ];
            }
        }

        $sanitizedRecommendations = [];
        if (!empty($result['recommendations']) && is_array($result['recommendations'])) {
            foreach ($result['recommendations'] as $rec) {
                $sanitizedRecommendations[] = [
                    'insight_index' => (int)($rec['insight_index'] ?? 0),
                    'title' => (string)($rec['title'] ?? ''),
                    'description' => (string)($rec['description'] ?? ''),
                    'action_type' => $this->normalizeActionType($rec['action_type'] ?? 'other'),
                    'estimated_impact' => isset($rec['estimated_impact']) ? (string)$rec['estimated_impact'] : '',
                    'difficulty' => $this->normalizeDifficulty($rec['difficulty'] ?? 'medium'),
                    'specific_actions' => isset($rec['specific_actions']) && is_array($rec['specific_actions']) ? $rec['specific_actions'] : [],
                ];
            }
        }

        return [
            'overall_performance' => [
                'score' => $score,
                'summary' => $summary,
            ],
            'insights' => $sanitizedInsights,
            'recommendations' => $sanitizedRecommendations,
        ];
    }

    protected function normalizeCategory(string $category): string
    {
        $allowed = ['performance', 'budget', 'targeting', 'creative', 'conversion'];
        $category = strtolower($category);
        return in_array($category, $allowed, true) ? $category : 'performance';
    }

    protected function normalizePriority(string $priority): string
    {
        $allowed = ['high', 'medium', 'low'];
        $priority = strtolower($priority);
        return in_array($priority, $allowed, true) ? $priority : 'medium';
    }

    protected function normalizeActionType(string $actionType): string
    {
        $allowed = ['budget_adjustment', 'keyword_addition', 'ad_copy_change', 'bid_adjustment', 'other'];
        $actionType = strtolower($actionType);
        return in_array($actionType, $allowed, true) ? $actionType : 'other';
    }

    protected function normalizeDifficulty(string $difficulty): string
    {
        $allowed = ['easy', 'medium', 'hard'];
        $difficulty = strtolower($difficulty);
        return in_array($difficulty, $allowed, true) ? $difficulty : 'medium';
    }

    protected function clampInt(int|float $value, int $min, int $max): int
    {
        $v = (int)$value;
        return max($min, min($max, $v));
    }

    protected function clampFloat(int|float|string $value, float $min, float $max): float
    {
        $v = (float)$value;
        if (!is_finite($v)) {
            $v = $min;
        }
        return max($min, min($max, $v));
    }

    /**
     * ジョブが失敗した場合の処理
     */
    public function failed(\Throwable $exception): void
    {
        $report = AnalysisReport::find($this->reportId);
        if ($report) {
            $report->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ]);
        }

        Log::error("Analysis report generation job failed", [
            'report_id' => $this->reportId,
            'error' => $exception->getMessage(),
        ]);
    }

    /**
     * フォールバック分析結果の構築
     */
    protected function buildFallbackResult(array $adData, array $analyticsData, ?string $rawText): array
    {
        // 簡易スコア: クリックやコンバージョン、CPAから大まかに評価
        $clicks = (int)($adData['clicks'] ?? 0);
        $conversions = (float)($adData['conversions'] ?? 0);
        $cpa = (float)($adData['cpa'] ?? 0);

        $score = 3;
        if ($conversions >= 50) {
            $score = 5;
        } elseif ($conversions >= 10) {
            $score = 4;
        } elseif ($clicks < 10) {
            $score = 2;
        }
        if ($cpa > 10000) {
            $score = max(1, $score - 1);
        }

        $summary = 'AIの詳細分析に失敗したため、基本メトリクスをもとに暫定評価を生成しました。';
        if ($rawText) {
            $summary .= ' AI出力(要約): ' . mb_substr($rawText, 0, 160) . '...';
        }

        return [
            'overall_performance' => [
                'score' => $score,
                'summary' => $summary,
            ],
            'insights' => [
                [
                    'category' => 'performance',
                    'priority' => 'medium',
                    'title' => '暫定評価: 基本メトリクスからの所見',
                    'description' => 'クリック、コンバージョン、CPAから大まかな改善余地を推定しています。',
                    'impact_score' => 5,
                    'confidence_score' => 0.5,
                    'data_points' => [
                        'impressions' => $adData['impressions'] ?? 0,
                        'clicks' => $clicks,
                        'conversions' => $conversions,
                        'cpa' => $cpa,
                        'ctr' => $adData['ctr'] ?? 0,
                    ],
                ],
            ],
            'recommendations' => [
                [
                    'insight_index' => 0,
                    'title' => 'トラフィック確保とCV最適化の両立',
                    'description' => 'クリックが少ない場合は入札/予算の見直し、CVが少ない場合はLP/コンバージョン設定の確認を行ってください。',
                    'action_type' => 'bid_adjustment',
                    'estimated_impact' => 'CVR向上とCPA安定化が期待できます',
                    'difficulty' => 'medium',
                    'specific_actions' => [
                        '低獲得のキャンペーンの入札戦略・予算配分を調整',
                        '検索語句・広告文の精査でCTR改善',
                        'CV計測設定とLPの摩擦点を確認',
                    ],
                ],
            ],
        ];
    }
}
