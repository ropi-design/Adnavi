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

            // DataAggregatorでデータを集約（実際のデータベースから取得）
            $aggregator = app(DataAggregator::class);
            $data = $aggregator->aggregate($report);

            // データが空または少ない場合はサンプルデータを使用
            $useSampleData = false;
            if (empty($data['ad_data']['impressions']) && empty($data['ad_data']['clicks'])) {
                Log::warning("No real data found, using sample data", [
                    'report_id' => $report->id,
                ]);
                $data = $this->generateSampleData();
                $useSampleData = true;
            }

            Log::info("Using data for Gemini API analysis", [
                'ad_data' => $data['ad_data'],
                'has_analytics' => !empty($data['analytics_data']),
                'has_keywords' => !empty($data['keyword_data']),
                'use_sample_data' => $useSampleData,
            ]);

            // Geminiで分析（生データと解析済みの両方を受け取る）
            $ai = $geminiService->analyzePerformance(
                $data['ad_data'],
                $data['analytics_data'],
                $data['keyword_data'] ?? []
            ) ?? [];

            $rawText = $ai['raw_text'] ?? null;
            $parsed = $ai['parsed'] ?? null;
            $prompt = $ai['prompt'] ?? null;

            // 解析失敗時は安全なフォールバックを構築
            if (!$parsed) {
                Log::warning("Gemini API parsing failed, using fallback result", [
                    'raw_text_length' => $rawText ? strlen($rawText) : 0,
                ]);
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
                    'sample_data_mode' => $useSampleData, // サンプルデータモードかどうかを記録
                ],
            ]);

            Log::info("AI analysis completed for report: {$report->id}", [
                'use_sample_data' => $useSampleData,
                'gemini_used' => !empty($rawText),
            ]);
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
     * サンプルデータを生成（Gemini APIに送る用）
     */
    protected function generateSampleData(): array
    {
        return [
            'ad_data' => [
                'impressions' => 59524,
                'clicks' => 1250,
                'cost' => 425000,
                'conversions' => 50,
                'ctr' => 2.1,
                'conversion_rate' => 4.0,
                'cpa' => 8500,
                'roas' => 2.35,
            ],
            'analytics_data' => [
                'sessions' => 3150,
                'users' => 2850,
                'bounce_rate' => 42.5,
                'conversion_rate' => 3.2,
            ],
            'keyword_data' => [
                'top_keywords' => [
                    [
                        'keyword' => 'サンプルキーワード1',
                        'match_type' => '完全一致',
                        'impressions' => 8500,
                        'clicks' => 285,
                        'cost' => 125000,
                        'conversions' => 12,
                        'conversion_value' => 150000,
                        'cpc' => 438.60,
                        'cvr' => 0.0421,
                        'cpa' => 10416.67,
                        'roas' => 1.20,
                    ],
                    [
                        'keyword' => 'サンプルキーワード2',
                        'match_type' => 'フレーズ一致',
                        'impressions' => 6200,
                        'clicks' => 198,
                        'cost' => 89000,
                        'conversions' => 9,
                        'conversion_value' => 110000,
                        'cpc' => 449.49,
                        'cvr' => 0.0455,
                        'cpa' => 9888.89,
                        'roas' => 1.24,
                    ],
                    [
                        'keyword' => 'サンプルキーワード3',
                        'match_type' => '完全一致',
                        'impressions' => 4800,
                        'clicks' => 165,
                        'cost' => 72000,
                        'conversions' => 8,
                        'conversion_value' => 95000,
                        'cpc' => 436.36,
                        'cvr' => 0.0485,
                        'cpa' => 9000.00,
                        'roas' => 1.32,
                    ],
                ],
                'poor_keywords' => [
                    [
                        'keyword' => 'サンプルキーワード4',
                        'match_type' => '広範一致',
                        'impressions' => 3500,
                        'clicks' => 125,
                        'cost' => 58000,
                        'conversions' => 2,
                        'conversion_value' => 25000,
                        'cpc' => 464.00,
                        'cvr' => 0.0160,
                        'cpa' => 29000.00,
                        'roas' => 0.43,
                    ],
                    [
                        'keyword' => 'サンプルキーワード5',
                        'match_type' => '広範一致',
                        'impressions' => 2800,
                        'clicks' => 98,
                        'cost' => 45000,
                        'conversions' => 1,
                        'conversion_value' => 12000,
                        'cpc' => 459.18,
                        'cvr' => 0.0102,
                        'cpa' => 45000.00,
                        'roas' => 0.27,
                    ],
                ],
            ],
        ];
    }

    /**
     * サンプル分析結果を生成（フォールバック用）
     */
    protected function generateSampleAnalysisResult(): array
    {
        return [
            'overall_performance' => [
                'score' => 4,
                'summary' => '広告パフォーマンスは良好な状態にありますが、いくつかの改善ポイントが特定されています。現在のインプレッション数59,524回、クリック数1,250回、コンバージョン数50件、総コスト425,000円、CPA 8,500円、ROAS 2.35の実績から、CTRが2.1%と業界平均2.5%を0.4ポイント下回っています。また、CPAが目標値7,000円を1,500円（17.6%）上回っており、予算効率に改善の余地があります。一方で、コンバージョン率は3.2%と前週比で6.7%向上しており、LP改善施策の効果が見られます。低CVRキーワードがクリック数の約18%を占めており、これらの最適化により、CPAを約15%改善できる見込みです。広告文の最適化、キーワードマッチタイプの見直し、ネガティブキーワードの追加、入札戦略の最適化により、総合的なパフォーマンス向上が期待できます。',
            ],
            'insights' => [
                [
                    'category' => 'performance',
                    'priority' => 'high',
                    'title' => '検索キャンペーンのCTRが2.1%と平均2.5%を下回り、改善余地あり',
                    'description' => '検索キャンペーンのCTRが2.1%と、業界平均の2.5%を0.4ポイント下回っています。現在のインプレッション数59,524回、クリック数1,250回の実績から、CTRを2.5%以上に改善することで、同インプレッション数で約238回の追加クリックが見込めます。根本原因として、広告文の訴求力不足、キーワードと広告文の関連性の低さ、広告表示位置の最適化不足が考えられます。影響を受ける指標はCTR、クリック数、インプレッション利用率、広告効率です。',
                    'impact_score' => 7,
                    'confidence_score' => 0.85,
                    'data_points' => [
                        'current_value' => 2.1,
                        'target_value' => 2.5,
                        'benchmark' => 2.5,
                        'affected_metrics' => ['CTR', 'クリック数', 'インプレッション利用率', '広告効率'],
                        'current_ctr' => 2.1,
                        'average_ctr' => 2.5,
                        'clicks' => 1250,
                        'impressions' => 59524,
                        'potential_additional_clicks' => 238,
                    ],
                ],
                [
                    'category' => 'budget',
                    'priority' => 'high',
                    'title' => 'CPAが8,500円と目標7,000円を上回り、予算効率に改善の余地',
                    'description' => '現在のCPAが8,500円と目標値7,000円を1,500円（17.6%）上回っています。総コスト425,000円、コンバージョン数50件の実績から、CPAを7,000円以下に改善することで、同予算で約60件以上のコンバージョン獲得（+10件、+20%）が見込めます。根本原因として、低効率なキーワードへの予算配分過多、入札単価の最適化不足、ターゲティング設定の見直しが必要です。影響を受ける指標はCPA、ROAS、予算効率、コンバージョン数です。',
                    'impact_score' => 8,
                    'confidence_score' => 0.9,
                    'data_points' => [
                        'current_value' => 8500,
                        'target_value' => 7000,
                        'benchmark' => 7000,
                        'affected_metrics' => ['CPA', 'ROAS', '予算効率', 'コンバージョン数'],
                        'current_cpa' => 8500,
                        'target_cpa' => 7000,
                        'spend' => 425000,
                        'conversions' => 50,
                        'potential_additional_conversions' => 10,
                    ],
                ],
                [
                    'category' => 'conversion',
                    'priority' => 'medium',
                    'title' => 'コンバージョン率が3.2%と前週比で6.7%向上、改善傾向を維持',
                    'description' => '現在のコンバージョン率が3.2%と前週の3.0%から0.2ポイント（6.7%）向上しています。現在のクリック数4,875回、コンバージョン数156件の実績から、この傾向を維持し、コンバージョン率を3.5%以上に改善することで、同クリック数で約171件以上のコンバージョン獲得（+15件、+9.4%）が見込めます。根本原因として、LP改善施策の効果、ユーザー体験の向上、コンバージョンファネルの最適化が考えられます。影響を受ける指標はCVR、コンバージョン数、クリックあたりのCV単価、コンバージョン効率です。',
                    'impact_score' => 7,
                    'confidence_score' => 0.85,
                    'data_points' => [
                        'current_value' => 3.2,
                        'target_value' => 3.5,
                        'benchmark' => 3.0,
                        'affected_metrics' => ['CVR', 'コンバージョン数', 'クリックあたりCV単価', 'コンバージョン効率'],
                        'current_cvr' => 3.2,
                        'previous_cvr' => 3.0,
                        'conversions' => 156,
                        'clicks' => 4875,
                        'potential_additional_conversions' => 15,
                    ],
                ],
                [
                    'category' => 'targeting',
                    'priority' => 'high',
                    'title' => '低CVRキーワードがクリック数の約18%を占め、効率低下の原因',
                    'description' => '低CVRキーワード（CVR 1.6%以下）がクリック数の約18%（223回）を占めており、CPAが29,000円と平均の約3.4倍となっています。これらのキーワードを除外または入札単価を下げることで、CPAを約15%改善し、予算を高効率キーワードに再配分できます。根本原因として、キーワードマッチタイプの最適化不足、ネガティブキーワードの不足、入札戦略の見直しが必要です。影響を受ける指標はCPA、CVR、予算効率、クリック品質です。',
                    'impact_score' => 9,
                    'confidence_score' => 0.9,
                    'data_points' => [
                        'current_value' => 29000,
                        'target_value' => 8500,
                        'benchmark' => 8500,
                        'affected_metrics' => ['CPA', 'CVR', '予算効率', 'クリック品質'],
                        'poor_keyword_clicks' => 223,
                        'total_clicks' => 1250,
                        'poor_keyword_cpa' => 29000,
                        'average_cpa' => 8500,
                        'potential_cpa_improvement' => 0.15,
                    ],
                ],
                [
                    'category' => 'creative',
                    'priority' => 'medium',
                    'title' => '広告文の最適化によりCTR改善が見込める',
                    'description' => '現在のCTRが2.1%と、最適化された広告文では通常2.5-3.0%のCTRが期待できます。広告文のA/Bテストを実施し、より魅力的な訴求点を追加することで、CTRを0.4ポイント以上改善できます。根本原因として、広告文の訴求力不足、ターゲット層への訴求ポイントの不一致、競合との差別化不足が考えられます。影響を受ける指標はCTR、クリック数、広告関連性スコア、広告効率です。',
                    'impact_score' => 6,
                    'confidence_score' => 0.8,
                    'data_points' => [
                        'current_value' => 2.1,
                        'target_value' => 2.5,
                        'benchmark' => 2.5,
                        'affected_metrics' => ['CTR', 'クリック数', '広告関連性スコア', '広告効率'],
                        'current_ctr' => 2.1,
                        'expected_ctr' => 2.5,
                        'potential_ctr_improvement' => 0.4,
                    ],
                ],
            ],
            'recommendations' => [
                [
                    'insight_index' => 0,
                    'title' => '広告文のA/Bテストを実施',
                    'description' => '検索キャンペーンの広告文を最適化し、より魅力的な訴求点を追加することで、CTRの向上が期待できます。現在のCTR 2.1%から目標2.5%以上への改善により、月間クリック数を約15%向上させることができます。',
                    'action_type' => 'ad_copy_change',
                    'estimated_impact' => 'CTR: 2.1% → 2.5%以上 (改善率: +19%) | クリック数: 1,250 → 1,488以上 (月間+238クリック) | インプレッション当たりのクリック数増加で広告効率が向上',
                    'difficulty' => 'easy',
                    'specific_actions' => [
                        '現在の広告文を分析し、パフォーマンスの高い要素を特定（現状CTR: 2.1%, インプレッション: 59,524）',
                        '新しい広告文バリエーションを5パターン作成（各パターンで3つのヘッドラインと2つの説明文をテスト）',
                        'A/Bテストを実施し、14日後に結果を評価（目標: CTR 2.5%以上、統計的有意性95%以上）',
                    ],
                ],
                [
                    'insight_index' => 1,
                    'title' => 'ディスプレイキャンペーンの入札戦略を見直し',
                    'description' => '効果的な配信先への予算集中と、入札設定の最適化により、CPAの改善が可能です。現在のCPA 8,500円から目標7,000円への削減により、月間コンバージョン数を約21%増加させることができます。',
                    'action_type' => 'bid_adjustment',
                    'estimated_impact' => 'CPA: 8,500円 → 7,000円以下 (削減額: 1,500円, 削減率: 17.6%) | 予算425,000円で: 現在50CV → 目標60CV以上 (+10CV, +20%) | 月間ROAS: 推定12%改善',
                    'difficulty' => 'medium',
                    'specific_actions' => [
                        '配信先別のCPAを分析し、効果的な配信先を特定（トップ3配信先で全体のCPAが6,200円の実績）',
                        '低効果な配信先の配信を停止または予算削減（CPA 10,000円超の配信先: 予算の30%を削減）',
                        '入札設定を調整し、CPA目標7,000円を設定（現状入札単価: 1.2円 → 目標1.0円、最大入札単価の15%削減）',
                    ],
                ],
                [
                    'insight_index' => 2,
                    'title' => 'LPの改善施策を継続',
                    'description' => 'コンバージョン率の向上を維持するため、現在のLP改善施策を継続し、さらなる最適化を検討します。現在のCVR 3.2%から目標3.5%への向上により、月間コンバージョン数を約9%増加させることができます。',
                    'action_type' => 'other',
                    'estimated_impact' => 'CVR: 3.2% → 3.5% (改善率: +9.4%) | コンバージョン数: 156 → 171以上 (月間+15CV) | 現在4,875クリックで: 156CV → 171CV以上 | クリックあたりのCV単価: 推定7%改善',
                    'difficulty' => 'medium',
                    'specific_actions' => [
                        'ヒートマップ分析により、ユーザーの行動を把握（現状: 離脱率68%、平均滞在時間: 45秒）',
                        'コンバージョンに至らない離脱ポイントを特定（「カート追加」→「購入完了」の間で42%が離脱）',
                        'LPのUI/UX改善を実施（ページ読み込み速度: 2.8秒 → 2.0秒以下、モバイル最適化、CTAボタンの配置最適化）',
                    ],
                ],
            ],
        ];
    }

    /**
     * 分析結果を保存
     */
    protected function saveResults(AnalysisReport $report, ?array $result): void
    {
        if (!$result) {
            Log::warning("saveResults: result is null or empty");
            return;
        }

        Log::info("saveResults: Starting to save results", [
            'report_id' => $report->id,
            'has_insights' => isset($result['insights']),
            'has_recommendations' => isset($result['recommendations']),
            'insights_count' => isset($result['insights']) ? count($result['insights']) : 0,
            'recommendations_count' => isset($result['recommendations']) ? count($result['recommendations']) : 0,
        ]);

        // Insightを作成
        $insights = [];
        if (isset($result['insights'])) {
            foreach ($result['insights'] as $index => $insightData) {
                try {
                    $insight = $this->createInsight($report, $insightData);
                    $insights[$index] = $insight;
                    Log::info("Insight created", [
                        'insight_id' => $insight->id,
                        'title' => $insight->title,
                        'index' => $index,
                    ]);
                } catch (\Exception $e) {
                    Log::error("Failed to create insight", [
                        'index' => $index,
                        'error' => $e->getMessage(),
                        'data' => $insightData,
                    ]);
                }
            }
        } else {
            Log::warning("No insights found in result");
        }

        // Recommendationを作成（Insightに紐付け）
        if (isset($result['recommendations'])) {
            foreach ($result['recommendations'] as $recData) {
                $insightIndex = $recData['insight_index'] ?? 0;
                $insight = $insights[$insightIndex] ?? $insights[0] ?? null;

                if ($insight) {
                    try {
                        $this->createRecommendation($insight, $recData);
                        Log::info("Recommendation created", [
                            'insight_id' => $insight->id,
                            'title' => $recData['title'] ?? 'N/A',
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Failed to create recommendation", [
                            'error' => $e->getMessage(),
                            'data' => $recData,
                        ]);
                    }
                } else {
                    Log::warning("No insight found for recommendation", [
                        'insight_index' => $insightIndex,
                        'available_indices' => array_keys($insights),
                    ]);
                }
            }
        } else {
            Log::warning("No recommendations found in result");
        }

        // 結果を保存
        $report->update([
            'overall_score' => $result['overall_performance']['score'] ?? null,
            'summary' => $result['overall_performance']['summary'] ?? null,
        ]);

        Log::info("saveResults: Completed", [
            'report_id' => $report->id,
            'insights_created' => count($insights),
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
            'data_points' => $data['data_points'] ?? null,
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
                $sanitizedRec = [
                    'insight_index' => (int)($rec['insight_index'] ?? 0),
                    'title' => (string)($rec['title'] ?? ''),
                    'description' => (string)($rec['description'] ?? ''),
                    'action_type' => $this->normalizeActionType($rec['action_type'] ?? 'other'),
                    'estimated_impact' => isset($rec['estimated_impact']) ? (string)$rec['estimated_impact'] : '',
                    'difficulty' => $this->normalizeDifficulty($rec['difficulty'] ?? 'medium'),
                    'specific_actions' => isset($rec['specific_actions']) && is_array($rec['specific_actions']) ? $rec['specific_actions'] : [],
                ];
                // keyword_suggestionsがあればそのまま追加
                if (isset($rec['keyword_suggestions']) && is_array($rec['keyword_suggestions'])) {
                    $sanitizedRec['keyword_suggestions'] = $rec['keyword_suggestions'];
                }
                $sanitizedRecommendations[] = $sanitizedRec;
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
