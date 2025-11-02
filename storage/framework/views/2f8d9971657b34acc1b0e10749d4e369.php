<?php

use App\Models\Insight;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

?>

<div class="p-6 lg:p-8 space-y-6 animate-fade-in">
    
    <div>
        <h1 class="text-4xl font-bold" style="color: #ffffff;">インサイト</h1>
        <p class="mt-1" style="color: #ffffff;">AIによる課題抽出</p>
    </div>

    
    <div class="card p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="col-span-2">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="インサイトを検索..."
                        class="pl-10 w-full px-4 py-2.5 rounded-lg transition-colors"
                        style="background-color: #ffffff; color: #000000; border: 2px solid #e5e7eb;" />
                </div>
            </div>

            <select wire:model.live="priorityFilter" class="w-full px-4 py-2.5 rounded-lg transition-colors"
                style="background-color: #ffffff; color: #000000; border: 2px solid #e5e7eb;">
                <option value="all">全ての優先度</option>
                <option value="high">高</option>
                <option value="medium">中</option>
                <option value="low">低</option>
            </select>

            <select wire:model.live="categoryFilter" class="w-full px-4 py-2.5 rounded-lg transition-colors"
                style="background-color: #ffffff; color: #000000; border: 2px solid #e5e7eb;">
                <option value="all">全てのカテゴリ</option>
                <option value="performance">パフォーマンス</option>
                <option value="budget">予算</option>
                <option value="targeting">ターゲティング</option>
                <option value="creative">クリエイティブ</option>
                <option value="conversion">コンバージョン</option>
            </select>
        </div>
    </div>

    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $insights; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $insight): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="p-6 hover:shadow-xl transition-all rounded-xl group"
                style="background-color: #ffffff; border: 2px solid #e5e7eb;">
                <div class="space-y-4">
                    
                    <!--[if BLOCK]><![endif]--><?php if($insight->analysisReport): ?>
                        <div class="pb-3 border-b border-gray-200">
                            <a href="/reports/<?php echo e($insight->analysisReport->id); ?>"
                                class="inline-flex items-center gap-2 text-xs text-blue-600 hover:text-blue-800 transition-colors mb-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span
                                    class="font-semibold"><?php echo e($insight->analysisReport->adAccount->account_name ?? 'レポート'); ?></span>
                            </a>
                            <p class="text-xs text-gray-500 mt-1">
                                <?php echo e($insight->analysisReport->start_date->isoFormat('YYYY/MM/DD')); ?> 〜
                                <?php echo e($insight->analysisReport->end_date->isoFormat('YYYY/MM/DD')); ?>

                            </p>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                    
                    <div class="flex items-start justify-between gap-3">
                        <a href="/insights/<?php echo e($insight->id); ?>" class="flex-1 min-w-0">
                            <h3 class="font-bold text-lg line-clamp-2 group-hover:text-blue-600 transition-colors"
                                style="color: #000000;">
                                <?php echo e($insight->title); ?>

                            </h3>
                        </a>

                        <?php
                            $priorityConfig = match ($insight->priority->value) {
                                'high' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => '高'],
                                'medium' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'label' => '中'],
                                'low' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => '低'],
                            };
                        ?>
                        <span
                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold <?php echo e($priorityConfig['bg']); ?> <?php echo e($priorityConfig['text']); ?>">
                            <?php echo e($priorityConfig['label']); ?>

                        </span>
                    </div>

                    
                    <div>
                        <?php
                            $categoryConfig = match ($insight->category->value) {
                                'performance' => ['label' => 'パフォーマンス', 'bg' => '#3b82f6', 'text' => '#ffffff'],
                                'budget' => ['label' => '予算', 'bg' => '#f59e0b', 'text' => '#ffffff'],
                                'targeting' => ['label' => 'ターゲティング', 'bg' => '#8b5cf6', 'text' => '#ffffff'],
                                'creative' => ['label' => 'クリエイティブ', 'bg' => '#ec4899', 'text' => '#ffffff'],
                                'conversion' => ['label' => 'コンバージョン', 'bg' => '#10b981', 'text' => '#ffffff'],
                            };
                        ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                            style="background-color: <?php echo e($categoryConfig['bg']); ?>; color: <?php echo e($categoryConfig['text']); ?>;">
                            <?php echo e($categoryConfig['label']); ?>

                        </span>
                    </div>

                    
                    <a href="/insights/<?php echo e($insight->id); ?>">
                        <p class="text-sm line-clamp-3" style="color: #000000;">
                            <?php echo e($insight->description); ?>

                        </p>
                    </a>

                    
                    <div class="flex items-center gap-4 pt-4 border-t border-gray-200">
                        <div class="flex items-center gap-2 text-sm">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span style="color: #000000;">インパクト:</span>
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
                                    default => ['label' => '小', 'bg' => 'bg-gray-100', 'text' => 'text-gray-800'],
                                };
                            ?>
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold <?php echo e($impactLabel['bg']); ?> <?php echo e($impactLabel['text']); ?>">
                                <?php echo e($impactLabel['label']); ?>

                            </span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span style="color: #000000;">信頼度:</span>
                            <span class="font-bold"
                                style="color: #000000;"><?php echo e(number_format($insight->confidence_score * 100)); ?>%</span>
                        </div>
                    </div>

                    
                    <!--[if BLOCK]><![endif]--><?php if($insight->recommendations_count > 0): ?>
                        <div class="pt-3 border-t border-gray-200">
                            <a href="/insights/<?php echo e($insight->id); ?>#recommendations"
                                class="inline-flex items-center gap-2 text-sm font-semibold text-purple-600 hover:text-purple-800 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                                <span>改善施策 (<?php echo e($insight->recommendations_count); ?>)</span>
                            </a>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-span-full">
                <div class="card">
                    <div class="text-center py-16 text-gray-500">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-6">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M6.343 6.343l-.707.707m12.728 0l-.707.707M6.343 17.657l-.707.707M17.657 6.343l-.707-.707m-12.728 0l-.707.707m12.728 12.728l-.707.707M17.657 17.657l-.707-.707M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">インサイトがありません</h3>
                        <p class="text-gray-500 mb-6">レポートを生成して、AIによる分析結果を確認しましょう</p>
                        <a href="/reports/generate" class="btn btn-primary inline-flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                            レポートを生成
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>

    
    <!--[if BLOCK]><![endif]--><?php if($insights->hasPages()): ?>
        <div class="card p-4">
            <?php echo e($insights->links()); ?>

        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div><?php /**PATH /var/www/html/resources/views/livewire/insights/insight-list.blade.php ENDPATH**/ ?>