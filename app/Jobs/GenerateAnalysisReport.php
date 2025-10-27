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

            // Geminiで分析
            $analysisResult = $geminiService->analyzePerformance(
                $data['ad_data'],
                $data['analytics_data']
            );

            // 結果を保存
            $this->saveResults($report, $analysisResult);

            // ステータスを完了に更新
            $report->update([
                'status' => 'completed',
                'analysis_result' => $analysisResult,
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

        // InsightとRecommendationを生成
        $insights = [];
        $recommendations = [];

        if (isset($result['insights'])) {
            foreach ($result['insights'] as $insightData) {
                $insights[] = $this->createInsight($report, $insightData);
            }
        }

        if (isset($result['recommendations'])) {
            foreach ($result['recommendations'] as $recData) {
                $recommendations[] = $this->createRecommendation($report, $recData);
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
    protected function createInsight(AnalysisReport $report, array $data): void
    {
        $report->insights()->create([
            'category' => $data['category'] ?? 'performance',
            'priority' => $data['priority'] ?? 'medium',
            'title' => $data['title'] ?? '',
            'description' => $data['description'] ?? '',
            'impact_score' => $data['impact_score'] ?? 5,
            'confidence_score' => $data['confidence_score'] ?? 0.7,
            'status' => 'new',
        ]);
    }

    /**
     * Recommendationレコードを作成
     */
    protected function createRecommendation(AnalysisReport $report, array $data): void
    {
        $report->recommendations()->create([
            'analysis_report_id' => $report->id,
            'insight_id' => null, // TODO: インデックスから取得
            'title' => $data['title'] ?? '',
            'description' => $data['description'] ?? '',
            'action_type' => $data['action_type'] ?? 'other',
            'estimated_impact' => $data['estimated_impact'] ?? '',
            'implementation_difficulty' => $data['difficulty'] ?? 'medium',
            'specific_actions' => $data['specific_actions'] ?? [],
            'status' => 'pending',
        ]);
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
}
