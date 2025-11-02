<?php

use App\Models\AnalysisReport;
use Illuminate\Support\Facades\Auth;

?>

<div class="p-6 lg:p-8 space-y-6 animate-fade-in">
    
    <div wire:loading wire:target="loadReport" class="flex flex-col items-center justify-center py-16">
        <svg class="w-12 h-12 text-blue-600 animate-spin mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        <p class="text-white font-medium">レポートを読み込んでいます...</p>
    </div>

    <!--[if BLOCK]><![endif]--><?php if($report && !$loading): ?>
        
        <div class="space-y-4">
            
            <a href="/reports" class="inline-flex items-center gap-2 text-white hover:text-blue-300 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                一覧に戻る
            </a>

            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h1 class="text-4xl font-bold text-white">分析レポートの<?php echo e($report->adAccount->account_name); ?></h1>
                    <p class="text-white mt-2">
                        <?php echo e(match ($report->report_type->value) {
                            'daily' => '日次レポート',
                            'weekly' => '週次レポート',
                            'monthly' => '月次レポート',
                            'custom' => 'カスタムレポート',
                        }); ?>

                        | <?php echo e(\Carbon\Carbon::parse($report->start_date)->isoFormat('YYYY年MM月DD日')); ?>

                        〜
                        <?php echo e(\Carbon\Carbon::parse($report->end_date)->isoFormat('YYYY年MM月DD日')); ?>

                    </p>
                </div>

                <div class="flex gap-3">
                    <?php
                        $statusConfig = match ($report->status->value) {
                            'completed' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'label' => '完了'],
                            'processing' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'label' => '処理中'],
                            'failed' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => '失敗'],
                            default => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => '待機中'],
                        };
                    ?>
                    <span
                        class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-bold <?php echo e($statusConfig['bg']); ?> <?php echo e($statusConfig['text']); ?>">
                        <?php echo e($statusConfig['label']); ?>

                    </span>
                </div>
            </div>
        </div>

        <!--[if BLOCK]><![endif]--><?php if($report->status->value === 'completed'): ?>
            
            <div class="card p-6">
                <h2 class="text-2xl font-bold text-white mb-4">概要</h2>
                <div class="space-y-2 text-white">
                    <p><strong>作成日:</strong> <?php echo e($report->created_at->isoFormat('YYYY年MM月DD日 HH:mm')); ?></p>
                    <p><strong>期間:</strong> <?php echo e($report->start_date->isoFormat('YYYY/MM/DD')); ?> 〜
                        <?php echo e($report->end_date->isoFormat('YYYY/MM/DD')); ?></p>
                    <!--[if BLOCK]><![endif]--><?php if($report->analyticsProperty): ?>
                        <p><strong>Analytics:</strong> <?php echo e($report->analyticsProperty->property_name); ?>を含む</p>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>

            
            <!--[if BLOCK]><![endif]--><?php if($report->insights->count() > 0): ?>
                <div class="card p-6">
                    <h2 class="text-2xl font-bold text-white mb-6">抽出されたインサイト</h2>

                    <div class="space-y-4">
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $report->insights; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $insight): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div
                                class="p-6 border-2 border-gray-200 rounded-xl hover:border-blue-300 hover:shadow-lg transition-all">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-3">
                                            <?php
                                                $priorityConfig = match ($insight->priority->value) {
                                                    'high' => [
                                                        'bg' => 'bg-red-100',
                                                        'text' => 'text-red-800',
                                                        'label' => '高',
                                                    ],
                                                    'medium' => [
                                                        'bg' => 'bg-yellow-100',
                                                        'text' => 'text-yellow-800',
                                                        'label' => '中',
                                                    ],
                                                    'low' => [
                                                        'bg' => 'bg-gray-100',
                                                        'text' => 'text-gray-800',
                                                        'label' => '低',
                                                    ],
                                                };
                                                $categoryLabel = match ($insight->category->value) {
                                                    'performance' => 'パフォーマンス',
                                                    'budget' => '予算',
                                                    'targeting' => 'ターゲティング',
                                                    'creative' => 'クリエイティブ',
                                                    'conversion' => 'コンバージョン',
                                                };
                                            ?>
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold <?php echo e($priorityConfig['bg']); ?> <?php echo e($priorityConfig['text']); ?>">
                                                <?php echo e($priorityConfig['label']); ?>

                                            </span>
                                            <span class="text-sm text-white"><?php echo e($categoryLabel); ?></span>
                                        </div>

                                        <h4 class="font-bold text-xl text-white mb-2"><?php echo e($insight->title); ?></h4>
                                        <p class="text-white mb-4 leading-relaxed"><?php echo e($insight->description); ?></p>

                                        <!--[if BLOCK]><![endif]--><?php if($insight->data_points): ?>
                                            <?php
                                                $dataPoints = is_array($insight->data_points)
                                                    ? $insight->data_points
                                                    : json_decode($insight->data_points, true);
                                            ?>
                                            <!--[if BLOCK]><![endif]--><?php if($dataPoints): ?>
                                                <div class="p-4 bg-blue-50 rounded-lg mb-4">
                                                    <h5 class="font-bold text-sm mb-3" style="color: #1e40af;">データポイント
                                                    </h5>
                                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                                        <!--[if BLOCK]><![endif]--><?php if(isset($dataPoints['current_value'])): ?>
                                                            <div>
                                                                <span class="text-gray-600">現在の値:</span>
                                                                <span class="font-bold ml-2" style="color: #1e40af;">
                                                                    <?php if(is_numeric($dataPoints['current_value'])): ?>
                                                                        <!--[if BLOCK]><![endif]--><?php if($dataPoints['current_value'] >= 1000): ?>
                                                                            <?php echo e(number_format($dataPoints['current_value'])); ?>

                                                                        <?php else: ?>
                                                                            <?php echo e(number_format($dataPoints['current_value'], 2)); ?>

                                                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                                    <?php else: ?>
                                                                        <?php echo e($dataPoints['current_value']); ?>

                                                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                                </span>
                                                            </div>
                                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                        <!--[if BLOCK]><![endif]--><?php if(isset($dataPoints['target_value'])): ?>
                                                            <div>
                                                                <span class="text-gray-600">目標値:</span>
                                                                <span class="font-bold ml-2" style="color: #16a34a;">
                                                                    <?php if(is_numeric($dataPoints['target_value'])): ?>
                                                                        <!--[if BLOCK]><![endif]--><?php if($dataPoints['target_value'] >= 1000): ?>
                                                                            <?php echo e(number_format($dataPoints['target_value'])); ?>

                                                                        <?php else: ?>
                                                                            <?php echo e(number_format($dataPoints['target_value'], 2)); ?>

                                                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                                    <?php else: ?>
                                                                        <?php echo e($dataPoints['target_value']); ?>

                                                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                                </span>
                                                            </div>
                                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                        <!--[if BLOCK]><![endif]--><?php if(isset($dataPoints['benchmark'])): ?>
                                                            <div>
                                                                <span class="text-gray-600">ベンチマーク:</span>
                                                                <span class="font-bold ml-2" style="color: #6b7280;">
                                                                    <?php if(is_numeric($dataPoints['benchmark'])): ?>
                                                                        <!--[if BLOCK]><![endif]--><?php if($dataPoints['benchmark'] >= 1000): ?>
                                                                            <?php echo e(number_format($dataPoints['benchmark'])); ?>

                                                                        <?php else: ?>
                                                                            <?php echo e(number_format($dataPoints['benchmark'], 2)); ?>

                                                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                                    <?php else: ?>
                                                                        <?php echo e($dataPoints['benchmark']); ?>

                                                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                                </span>
                                                            </div>
                                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                        <!--[if BLOCK]><![endif]--><?php if(isset($dataPoints['affected_metrics']) && is_array($dataPoints['affected_metrics'])): ?>
                                                            <div class="col-span-2">
                                                                <span class="text-gray-600">影響を受ける指標:</span>
                                                                <div class="flex flex-wrap gap-2 mt-1">
                                                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $dataPoints['affected_metrics']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metric): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                        <span
                                                                            class="px-2 py-1 bg-blue-100 rounded text-xs font-semibold"
                                                                            style="color: #1e40af;">
                                                                            <?php echo e($metric); ?>

                                                                        </span>
                                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                                                </div>
                                                            </div>
                                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                </div>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                                        <div class="flex items-center gap-6 mt-4 text-sm">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                </svg>
                                                <span class="text-white">インパクト:</span>
                                                <?php
                                                    $impactLabel = match (true) {
                                                        $insight->impact_score >= 8 => [
                                                            'label' => '大',
                                                            'bg' => 'bg-red-100',
                                                            'text' => 'text-red-800',
                                                        ],
                                                        $insight->impact_score >= 4 => [
                                                            'label' => '中',
                                                            'bg' => 'bg-yellow-100',
                                                            'text' => 'text-yellow-800',
                                                        ],
                                                        default => [
                                                            'label' => '小',
                                                            'bg' => 'bg-gray-100',
                                                            'text' => 'text-gray-800',
                                                        ],
                                                    };
                                                ?>
                                                <span
                                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold <?php echo e($impactLabel['bg']); ?> <?php echo e($impactLabel['text']); ?>">
                                                    <?php echo e($impactLabel['label']); ?>

                                                </span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span class="text-white">信頼度:</span>
                                                <span
                                                    class="font-bold text-white"><?php echo e(number_format($insight->confidence_score * 100)); ?>%</span>
                                            </div>
                                            <!--[if BLOCK]><![endif]--><?php if($insight->recommendations && $insight->recommendations->count() > 0): ?>
                                                <div class="flex items-center gap-2">
                                                    <svg class="w-4 h-4 text-purple-600" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                                    </svg>
                                                    <span class="text-white">改善施策:</span>
                                                    <span
                                                        class="font-bold text-white"><?php echo e($insight->recommendations->count()); ?>件</span>
                                                </div>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-2">
                                        <a href="/insights/<?php echo e($insight->id); ?>" class="btn btn-primary text-sm">
                                            詳細
                                        </a>
                                        <!--[if BLOCK]><![endif]--><?php if($insight->recommendations && $insight->recommendations->count() > 0): ?>
                                            <a href="/insights/<?php echo e($insight->id); ?>#recommendations"
                                                class="btn text-sm inline-flex items-center justify-center gap-2"
                                                style="background-color: #9333ea; color: #ffffff; border: 2px solid #9333ea;">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                                </svg>
                                                改善施策を見る
                                            </a>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            
            <!--[if BLOCK]><![endif]--><?php if($report->recommendations->count() > 0): ?>
                <div class="card p-6">
                    <h2 class="text-2xl font-bold text-white mb-6">改善施策</h2>
                    <div class="space-y-4">
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $report->recommendations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $recommendation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="p-6 border-2 border-gray-200 rounded-xl hover:border-blue-300 hover:shadow-lg transition-all"
                                style="background-color: rgba(255, 255, 255, 0.05);">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-3">
                                            <?php
                                                $statusConfig = match ($recommendation->status->value) {
                                                    'pending' => [
                                                        'bg' => 'bg-gray-100',
                                                        'text' => 'text-gray-800',
                                                        'label' => '未着手',
                                                    ],
                                                    'in_progress' => [
                                                        'bg' => 'bg-blue-100',
                                                        'text' => 'text-blue-800',
                                                        'label' => '実施中',
                                                    ],
                                                    'implemented' => [
                                                        'bg' => 'bg-green-100',
                                                        'text' => 'text-green-800',
                                                        'label' => '実施済み',
                                                    ],
                                                    'dismissed' => [
                                                        'bg' => 'bg-red-100',
                                                        'text' => 'text-red-800',
                                                        'label' => '却下',
                                                    ],
                                                };
                                                $difficultyLabel = match ($recommendation->implementation_difficulty) {
                                                    'easy' => '簡単',
                                                    'medium' => '普通',
                                                    'hard' => '難しい',
                                                };
                                            ?>
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold <?php echo e($statusConfig['bg']); ?> <?php echo e($statusConfig['text']); ?>">
                                                <?php echo e($statusConfig['label']); ?>

                                            </span>
                                            <span class="text-sm text-white">難易度: <?php echo e($difficultyLabel); ?></span>
                                        </div>

                                        <h4 class="font-bold text-xl text-white mb-2"><?php echo e($recommendation->title); ?>

                                        </h4>
                                        <p class="text-white mb-4"><?php echo e($recommendation->description); ?></p>

                                        <!--[if BLOCK]><![endif]--><?php if($recommendation->estimated_impact): ?>
                                            <div class="p-4 bg-blue-50 rounded-lg mb-4">
                                                <?php
                                                    $impactLines = explode(' | ', $recommendation->estimated_impact);
                                                ?>
                                                <div class="space-y-2">
                                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $impactLines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <div class="flex items-start gap-2">
                                                            <svg class="w-4 h-4 text-blue-600 flex-shrink-0 mt-1"
                                                                fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                                            </svg>
                                                            <p class="text-sm font-semibold" style="color: #1e40af;">
                                                                <?php echo e($line); ?></p>
                                                        </div>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                                </div>
                                            </div>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                                        <!--[if BLOCK]><![endif]--><?php if($recommendation->specific_actions && count($recommendation->specific_actions) > 0): ?>
                                            <div class="mt-4">
                                                <p class="text-sm font-semibold text-white mb-2">実施手順:</p>
                                                <ul class="space-y-2">
                                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $recommendation->specific_actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <li class="flex items-start gap-2 text-sm text-white">
                                                            <span
                                                                class="flex-shrink-0 w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-bold mt-0.5">
                                                                <?php echo e($index + 1); ?>

                                                            </span>
                                                            <span><?php echo e($action); ?></span>
                                                        </li>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                                </ul>
                                            </div>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>

                                    <a href="/recommendations/<?php echo e($recommendation->id); ?>"
                                        class="btn btn-primary text-sm" style="color: #000000 !important;">
                                        詳細
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        <?php elseif($report->status->value === 'failed'): ?>
            <div class="card p-6">
                <div class="p-6 bg-red-50 border-l-4 border-red-500 rounded-lg overflow-hidden">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-red-600 flex-shrink-0 mt-1" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="flex-1 min-w-0 overflow-hidden">
                            <p class="font-bold text-red-900">レポート生成に失敗しました</p>
                            <p class="mt-2 text-sm text-red-800 break-all">
                                <?php echo e($report->error_message); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="text-center py-16">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-100 rounded-full mb-6">
                        <svg class="w-10 h-10 text-blue-600 animate-spin" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-xl font-semibold text-white">レポートを生成中です...</p>
                    <p class="text-white mt-2">完了次第、通知いたします</p>
                </div>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div><?php /**PATH /var/www/html/resources/views/livewire/reports/report-detail.blade.php ENDPATH**/ ?>