<?php

use App\Models\AnalysisReport;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

?>

<div class="p-6 lg:p-8 space-y-6 animate-fade-in">
    
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-4xl font-bold" style="color: #ffffff;">分析レポート</h1>
            <p class="mt-1" style="color: #ffffff;">AI分析レポートの一覧</p>
        </div>

        <a href="/reports/generate"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-colors border-2"
            style="background-color: #ffffff; color: #000000; border-color: #e5e7eb;">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            新規作成
        </a>
    </div>

    
    <div class="card p-6">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="アカウント名で検索..."
                        class="pl-10 w-full px-4 py-2.5 rounded-lg transition-colors"
                        style="background-color: #ffffff; color: #000000; border: 2px solid #e5e7eb;" />
                </div>
            </div>

            <select wire:model.live="statusFilter" class="w-full px-4 py-2.5 rounded-lg transition-colors"
                style="background-color: #ffffff; color: #000000; border: 2px solid #e5e7eb;">
                <option value="all">全てのステータス</option>
                <option value="pending">待機中</option>
                <option value="processing">処理中</option>
                <option value="completed">完了</option>
                <option value="failed">失敗</option>
            </select>
        </div>
    </div>

    
    <div class="space-y-4">
        <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="card p-6 hover:shadow-xl transition-all">
                <div class="flex flex-col lg:flex-row lg:items-start gap-4">
                    
                    <div class="flex-shrink-0">
                        <?php
                            $statusConfig = match ($report->status->value) {
                                'completed' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'label' => '完了'],
                                'processing' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'label' => '処理中'],
                                'failed' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => '失敗'],
                                default => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => '待機中'],
                            };
                        ?>
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold <?php echo e($statusConfig['bg']); ?> <?php echo e($statusConfig['text']); ?>">
                            <?php echo e($statusConfig['label']); ?>

                        </span>
                    </div>

                    
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start gap-3 mb-3">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-bold text-xl" style="color: #ffffff;">
                                    <?php echo e($report->adAccount->account_name); ?>

                                </h3>

                                <div class="flex flex-wrap items-center gap-3 text-sm text-gray-600 mt-2">
                                    <span class="inline-flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                        <?php echo e(match ($report->report_type->value) {
                                            'daily' => '日次レポート',
                                            'weekly' => '週次レポート',
                                            'monthly' => '月次レポート',
                                            'custom' => 'カスタムレポート',
                                        }); ?>

                                    </span>
                                    <span class="text-gray-400">|</span>
                                    <span class="inline-flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <?php echo e(\Carbon\Carbon::parse($report->start_date)->isoFormat('YYYY/MM/DD')); ?>

                                        〜
                                        <?php echo e(\Carbon\Carbon::parse($report->end_date)->isoFormat('YYYY/MM/DD')); ?>

                                    </span>
                                </div>

                                <div class="text-xs text-gray-500 mt-2">
                                    作成: <?php echo e($report->created_at->diffForHumans()); ?>

                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="flex gap-2 flex-shrink-0">
                        <!--[if BLOCK]><![endif]--><?php if($report->status->value === 'completed'): ?>
                            <a href="/reports/<?php echo e($report->id); ?>"
                                class="inline-flex items-center px-4 py-2 rounded-lg font-semibold text-sm transition-colors border-2"
                                style="background-color: #1e40af; color: #ffffff; border-color: #1e3a8a;">
                                <span class="inline-flex items-center px-2 py-1 rounded-md font-semibold text-xs mr-2"
                                    style="background-color: rgba(255, 255, 255, 0.2); color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.3);">
                                    詳細を見る
                                </span>
                            </a>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                        <!--[if BLOCK]><![endif]--><?php if($report->status->value === 'failed'): ?>
                            <a href="/reports/<?php echo e($report->id); ?>"
                                class="inline-flex items-center px-4 py-2 rounded-lg font-semibold text-sm transition-colors border-2"
                                style="background-color: #1e40af; color: #ffffff; border-color: #1e3a8a;">
                                <span class="inline-flex items-center px-2 py-1 rounded-md font-semibold text-xs mr-2"
                                    style="background-color: rgba(255, 255, 255, 0.2); color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.3);">
                                    詳細を見る
                                </span>
                            </a>
                            <button
                                class="inline-flex items-center px-4 py-2 rounded-lg font-semibold text-sm transition-colors border-2"
                                style="background-color: #6b7280; color: #ffffff; border-color: #4b5563;">
                                再試行
                            </button>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                        <!--[if BLOCK]><![endif]--><?php if($deleteConfirmId === $report->id): ?>
                            <div class="flex gap-2 items-center">
                                <button wire:click="deleteReport(<?php echo e($report->id); ?>)"
                                    class="inline-flex items-center px-3 py-2 rounded-lg font-semibold text-sm transition-colors border-2 hover:opacity-80"
                                    style="background-color: #dc2626; color: #ffffff; border-color: #b91c1c;">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    削除
                                </button>
                                <button wire:click="cancelDelete()"
                                    class="inline-flex items-center px-3 py-2 rounded-lg font-semibold text-sm transition-colors border-2 hover:opacity-80"
                                    style="background-color: #6b7280; color: #ffffff; border-color: #4b5563;">
                                    キャンセル
                                </button>
                            </div>
                        <?php else: ?>
                            <button wire:click="confirmDelete(<?php echo e($report->id); ?>)"
                                class="inline-flex items-center px-3 py-2 rounded-lg font-semibold text-sm transition-colors border-2 hover:opacity-80"
                                style="background-color: #dc2626; color: #ffffff; border-color: #b91c1c;">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="card">
                <div class="text-center py-16 text-gray-500">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-6">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">レポートがありません</h3>
                    <p class="text-gray-500 mb-6">最初のAIレポートを作成しましょう</p>
                    <a href="/reports/generate" class="btn btn-primary inline-flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v16m8-8H4" />
                        </svg>
                        レポートを作成
                    </a>
                </div>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>

    
    <!--[if BLOCK]><![endif]--><?php if($reports->hasPages()): ?>
        <div class="card p-4">
            <?php echo e($reports->links()); ?>

        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div><?php /**PATH /var/www/html/resources/views/livewire/reports/report-list.blade.php ENDPATH**/ ?>