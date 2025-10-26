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

            // TODO: DataAggregatorでデータを集約
            // $aggregator = app(DataAggregator::class);
            // $data = $aggregator->aggregate($report);

            // Geminiで分析
            // $analysisResult = $geminiService->analyzePerformance($data);

            // 結果を保存
            // $this->saveResults($report, $analysisResult);

            // ステータスを完了に更新
            $report->update([
                'status' => 'completed',
                'analysis_result' => [], // TODO: Gemini の結果
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
