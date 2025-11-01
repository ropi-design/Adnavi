<?php

namespace App\Services\Analysis;

use App\Models\AdMetricsDaily;
use App\Models\AnalysisReport;
use App\Models\AnalyticsMetricsDaily;
use App\Models\KeywordMetricsDaily;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DataAggregator
{
    /**
     * レポート用にデータを集約
     */
    public function aggregate(AnalysisReport $report): array
    {
        $startDate = Carbon::parse($report->start_date);
        $endDate = Carbon::parse($report->end_date);

        // 広告データの集約
        $adData = $this->aggregateAdData($report->ad_account_id, $startDate, $endDate);

        // Analyticsデータの集約（オプション）
        $analyticsData = [];
        if ($report->analytics_property_id) {
            $analyticsData = $this->aggregateAnalyticsData($report->analytics_property_id, $startDate, $endDate);
        }

        // キーワードデータ（トップ/改善余地）
        $keywordData = $this->aggregateKeywordData($report->ad_account_id, $startDate, $endDate);

        return [
            'ad_data' => $adData,
            'analytics_data' => $analyticsData,
            'keyword_data' => $keywordData,
        ];
    }

    /**
     * 広告データを集約
     */
    protected function aggregateAdData(int $adAccountId, Carbon $startDate, Carbon $endDate): array
    {
        // まずad_accountに紐づくcampaign_idsを取得
        $campaignIds = DB::table('campaigns')
            ->where('ad_account_id', $adAccountId)
            ->pluck('id');

        if ($campaignIds->isEmpty()) {
            return [
                'impressions' => 0,
                'clicks' => 0,
                'cost' => 0,
                'conversions' => 0,
                'ctr' => 0,
                'conversion_rate' => 0,
                'cpa' => 0,
                'roas' => 0,
            ];
        }

        // キャンペーンIDで広告メトリクスを集約
        // 注意: ad_metrics_dailyにはconversion_rateカラムがないため、計算で取得する
        // テーブル名を明示して、JOINが生成されないようにする
        $metrics = DB::table('ad_metrics_daily')
            ->whereIn('campaign_id', $campaignIds)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->select(
                DB::raw('SUM(impressions) as total_impressions'),
                DB::raw('SUM(clicks) as total_clicks'),
                DB::raw('SUM(cost) as total_cost'),
                DB::raw('SUM(conversions) as total_conversions'),
                DB::raw('AVG(ctr) as avg_ctr')
            )
            ->first();

        $totalImpressions = $metrics->total_impressions ?? 0;
        $totalClicks = $metrics->total_clicks ?? 0;
        $totalCost = $metrics->total_cost ?? 0;
        $totalConversions = $metrics->total_conversions ?? 0;
        $ctr = $metrics->avg_ctr ?? 0;

        // コンバージョン率を計算
        $conversionRate = $totalClicks > 0 ? ($totalConversions / $totalClicks * 100) : 0;

        // CPA計算
        $cpa = $totalConversions > 0 ? ($totalCost / $totalConversions) : 0;

        // ROAS計算（総売上がないため、簡易計算）
        $roas = $totalCost > 0 ? ($totalConversions * 1000 / $totalCost) : 0;

        return [
            'impressions' => $totalImpressions,
            'clicks' => $totalClicks,
            'cost' => $totalCost,
            'conversions' => $totalConversions,
            'ctr' => $ctr,
            'conversion_rate' => $conversionRate,
            'cpa' => $cpa,
            'roas' => $roas,
        ];
    }

    /**
     * Analyticsデータを集約
     */
    protected function aggregateAnalyticsData(int $propertyId, Carbon $startDate, Carbon $endDate): array
    {
        $metrics = AnalyticsMetricsDaily::where('analytics_property_id', $propertyId)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->select(
                DB::raw('SUM(sessions) as total_sessions'),
                DB::raw('SUM(users) as total_users'),
                DB::raw('AVG(bounce_rate) as avg_bounce_rate'),
                DB::raw('AVG(conversion_rate) as avg_conversion_rate')
            )
            ->first();

        return [
            'sessions' => $metrics->total_sessions ?? 0,
            'users' => $metrics->total_users ?? 0,
            'bounce_rate' => $metrics->avg_bounce_rate ?? 0,
            'conversion_rate' => $metrics->avg_conversion_rate ?? 0,
        ];
    }

    /**
     * キーワードデータ（トップ・改善余地）
     */
    protected function aggregateKeywordData(int $adAccountId, Carbon $startDate, Carbon $endDate): array
    {
        $campaignIds = DB::table('campaigns')
            ->where('ad_account_id', $adAccountId)
            ->pluck('id');

        if ($campaignIds->isEmpty()) {
            return ['top_keywords' => [], 'poor_keywords' => []];
        }

        $rows = KeywordMetricsDaily::whereIn('campaign_id', $campaignIds)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->select(
                'keyword',
                'match_type',
                DB::raw('SUM(impressions) as imps'),
                DB::raw('SUM(clicks) as clicks'),
                DB::raw('SUM(cost) as cost'),
                DB::raw('SUM(conversions) as convs'),
                DB::raw('SUM(conversion_value) as conv_value')
            )
            ->groupBy('keyword', 'match_type')
            ->get()
            ->map(function ($r) {
                $cpc = $r->clicks > 0 ? $r->cost / $r->clicks : 0;
                $cvr = $r->clicks > 0 ? $r->convs / $r->clicks : 0;
                $cpa = $r->convs > 0 ? $r->cost / $r->convs : 0;
                $roas = $r->cost > 0 ? ($r->conv_value / $r->cost) : 0;
                return [
                    'keyword' => $r->keyword,
                    'match_type' => $r->match_type,
                    'impressions' => (int)$r->imps,
                    'clicks' => (int)$r->clicks,
                    'cost' => (float)$r->cost,
                    'conversions' => (float)$r->convs,
                    'conversion_value' => (float)$r->conv_value,
                    'cpc' => (float)round($cpc, 2),
                    'cvr' => (float)round($cvr, 4),
                    'cpa' => (float)round($cpa, 2),
                    'roas' => (float)round($roas, 4),
                ];
            });

        $top = $rows->sortByDesc('clicks')->take(5)->values()->all();
        $poor = $rows->filter(fn($r) => $r['clicks'] >= 10)->sortBy('cvr')->take(5)->values()->all();

        return [
            'top_keywords' => $top,
            'poor_keywords' => $poor,
        ];
    }
}
