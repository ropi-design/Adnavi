<?php

use App\Models\AnalyticsProperty;
use Illuminate\Support\Facades\Auth;

?>

<div class="p-6 lg:p-8 space-y-6 animate-fade-in">
    
    <div wire:loading wire:target="loadProperty" class="flex flex-col items-center justify-center py-16">
        <svg class="w-12 h-12 text-blue-600 animate-spin mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        <p class="font-medium" style="color: #ffffff;">プロパティを読み込んでいます...</p>
    </div>

    <!--[if BLOCK]><![endif]--><?php if(!$loading): ?>
        <!--[if BLOCK]><![endif]--><?php if($property): ?>
            
            <div class="mb-4">
                <a href="/accounts/analytics/list"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-colors border-2"
                    style="background-color: #ffffff; color: #000000; border-color: #e5e7eb;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    プロパティ一覧
                </a>
            </div>

            
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="p-4 bg-purple-100 rounded-lg flex-shrink-0">
                        <svg class="w-10 h-10 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-4xl font-bold mb-2" style="color: #ffffff;"><?php echo e($property->property_name); ?></h1>
                        <p class="text-sm font-mono mb-2" style="color: #9ca3af;">ID: <?php echo e($property->property_id); ?></p>
                        <?php
                            $statusConfig = $property->is_active
                                ? ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'label' => 'アクティブ']
                                : ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => '非アクティブ'];
                        ?>
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold <?php echo e($statusConfig['bg']); ?> <?php echo e($statusConfig['text']); ?>">
                            <?php echo e($statusConfig['label']); ?>

                        </span>
                    </div>
                </div>
            </div>

            
            <div class="card p-6">
                <h2 class="text-2xl font-bold mb-4" style="color: #ffffff;">基本情報</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-400" style="color: #9ca3af;">タイムゾーン:</span>
                        <span class="font-bold ml-2" style="color: #ffffff;"><?php echo e($property->timezone); ?></span>
                    </div>
                    <div>
                        <span class="text-gray-400" style="color: #9ca3af;">Googleアカウント:</span>
                        <span class="font-bold ml-2" style="color: #ffffff;">
                            <?php echo e($property->googleAccount ? $property->googleAccount->email : 'N/A'); ?>

                        </span>
                    </div>
                    <!--[if BLOCK]><![endif]--><?php if($property->last_synced_at): ?>
                        <div>
                            <span class="text-gray-400" style="color: #9ca3af;">最終同期:</span>
                            <span class="font-bold ml-2" style="color: #ffffff;">
                                <?php echo e($property->last_synced_at->isoFormat('YYYY年MM月DD日 HH:mm')); ?>

                            </span>
                        </div>
                        <div>
                            <span class="text-gray-400" style="color: #9ca3af;">最終同期からの経過:</span>
                            <span class="font-bold ml-2" style="color: #ffffff;">
                                <?php echo e($property->last_synced_at->diffForHumans()); ?>

                            </span>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    <div>
                        <span class="text-gray-400" style="color: #9ca3af;">作成日:</span>
                        <span class="font-bold ml-2" style="color: #ffffff;">
                            <?php echo e($property->created_at->isoFormat('YYYY年MM月DD日 HH:mm')); ?>

                        </span>
                    </div>
                </div>
            </div>

            
            <!--[if BLOCK]><![endif]--><?php if($property->analyticsMetricsDaily->count() > 0): ?>
                <div class="card p-6">
                    <h2 class="text-2xl font-bold mb-4" style="color: #ffffff;">日次メトリクス</h2>
                    <p class="text-sm mb-4" style="color: #9ca3af;">直近10件の日次メトリクス</p>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b" style="border-color: #374151;">
                                    <th class="text-left py-2 px-4 font-semibold" style="color: #ffffff;">日付</th>
                                    <th class="text-right py-2 px-4 font-semibold" style="color: #ffffff;">セッション</th>
                                    <th class="text-right py-2 px-4 font-semibold" style="color: #ffffff;">ユーザー</th>
                                    <th class="text-right py-2 px-4 font-semibold" style="color: #ffffff;">ページビュー</th>
                                    <th class="text-right py-2 px-4 font-semibold" style="color: #ffffff;">直帰率</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $property->analyticsMetricsDaily->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metrics): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="border-b" style="border-color: #374151;">
                                        <td class="py-2 px-4" style="color: #ffffff;">
                                            <?php echo e($metrics->date->isoFormat('YYYY/MM/DD')); ?>

                                        </td>
                                        <td class="text-right py-2 px-4" style="color: #ffffff;">
                                            <?php echo e(number_format($metrics->sessions)); ?>

                                        </td>
                                        <td class="text-right py-2 px-4" style="color: #ffffff;">
                                            <?php echo e(number_format($metrics->users)); ?>

                                        </td>
                                        <td class="text-right py-2 px-4" style="color: #ffffff;">
                                            <?php echo e(number_format($metrics->pageviews)); ?>

                                        </td>
                                        <td class="text-right py-2 px-4" style="color: #ffffff;">
                                            <?php echo e(number_format($metrics->bounce_rate * 100, 2)); ?>%
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            
            <!--[if BLOCK]><![endif]--><?php if($property->analysisReports->count() > 0): ?>
                <div class="card p-6">
                    <h2 class="text-2xl font-bold mb-4" style="color: #ffffff;">関連レポート</h2>
                    <p class="text-sm mb-4" style="color: #9ca3af;">このプロパティを使用したレポート</p>
                    <div class="space-y-3">
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $property->analysisReports->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="/reports/<?php echo e($report->id); ?>"
                                class="block p-4 rounded-lg border-2 transition-all hover:shadow-md"
                                style="background-color: #1f2937; border-color: #374151;">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h3 class="font-bold text-lg mb-1" style="color: #ffffff;">
                                            <?php echo e($report->adAccount->account_name); ?>

                                        </h3>
                                        <p class="text-sm mb-2" style="color: #9ca3af;">
                                            <?php echo e($report->start_date->isoFormat('YYYY年MM月DD日')); ?> 〜
                                            <?php echo e($report->end_date->isoFormat('YYYY年MM月DD日')); ?>

                                        </p>
                                        <!--[if BLOCK]><![endif]--><?php if($report->status->value === 'failed' && $report->error_message): ?>
                                            <p class="text-xs mt-1 p-2 rounded"
                                                style="color: #fca5a5; background-color: rgba(153, 27, 27, 0.2);">
                                                <?php echo e(Str::limit($report->error_message, 100)); ?>

                                            </p>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <?php
                                        $statusConfig = match ($report->status->value) {
                                            'completed' => [
                                                'bg' => 'bg-green-100',
                                                'text' => 'text-green-800',
                                                'label' => '完了',
                                            ],
                                            'processing' => [
                                                'bg' => 'bg-blue-100',
                                                'text' => 'text-blue-800',
                                                'label' => '処理中',
                                            ],
                                            'failed' => [
                                                'bg' => 'bg-red-100',
                                                'text' => 'text-red-800',
                                                'label' => '失敗',
                                            ],
                                            default => [
                                                'bg' => 'bg-gray-100',
                                                'text' => 'text-gray-800',
                                                'label' => '待機中',
                                            ],
                                        };
                                    ?>
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold ml-4 <?php echo e($statusConfig['bg']); ?> <?php echo e($statusConfig['text']); ?>">
                                        <?php echo e($statusConfig['label']); ?>

                                    </span>
                                </div>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        <?php else: ?>
            
            <div class="card">
                <div class="text-center py-16 text-gray-500">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-6">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2" style="color: #ffffff;">Analyticsプロパティが登録されていません</h3>
                    <p class="text-gray-500 mb-6" style="color: #9ca3af;">Googleアカウントと連携して始めましょう</p>
                    <a href="/accounts/google" class="btn btn-primary inline-flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                        Googleアカウントと連携
                    </a>
                </div>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div><?php /**PATH /var/www/html/resources/views/livewire/accounts/analytics-property-detail.blade.php ENDPATH**/ ?>