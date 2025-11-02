<?php

use App\Models\Recommendation;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

?>

<div class="p-6 lg:p-8 space-y-6 animate-fade-in">
    
    <div>
        <h1 class="text-4xl font-bold" style="color: #ffffff;">改善施策</h1>
        <p class="mt-1" style="color: #ffffff;">AIが提案する具体的な改善アクション</p>
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
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="施策を検索..."
                        class="pl-10 w-full px-4 py-2.5 rounded-lg transition-colors"
                        style="background-color: #ffffff; color: #000000; border: 2px solid #e5e7eb;" />
                </div>
            </div>

            <select wire:model.live="statusFilter" class="w-full px-4 py-2.5 rounded-lg transition-colors"
                style="background-color: #ffffff; color: #000000; border: 2px solid #e5e7eb;">
                <option value="all">全てのステータス</option>
                <option value="pending">未着手</option>
                <option value="in_progress">実施中</option>
                <option value="implemented">実施済み</option>
                <option value="dismissed">却下</option>
            </select>

            <select wire:model.live="difficultyFilter" class="w-full px-4 py-2.5 rounded-lg transition-colors"
                style="background-color: #ffffff; color: #000000; border: 2px solid #e5e7eb;">
                <option value="all">全ての難易度</option>
                <option value="easy">簡単</option>
                <option value="medium">普通</option>
                <option value="hard">難しい</option>
            </select>
        </div>
    </div>

    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $recommendations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $recommendation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="p-6 hover:shadow-xl transition-all rounded-xl"
                style="background-color: #ffffff; border: 2px solid #e5e7eb;">
                <div class="space-y-4">
                    
                    <!--[if BLOCK]><![endif]--><?php if($recommendation->insight): ?>
                        <div class="pb-3 border-b border-gray-200">
                            <a href="/insights/<?php echo e($recommendation->insight->id); ?>"
                                class="inline-flex items-center gap-2 text-xs text-purple-600 hover:text-purple-800 transition-colors mb-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M6.343 6.343l-.707.707m12.728 0l-.707.707M6.343 17.657l-.707.707M17.657 6.343l-.707-.707m-12.728 0l-.707.707m12.728 12.728l-.707.707M17.657 17.657l-.707-.707M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="font-semibold"><?php echo e($recommendation->insight->title); ?></span>
                            </a>
                            <!--[if BLOCK]><![endif]--><?php if($recommendation->insight->analysisReport): ?>
                                <p class="text-xs text-gray-500 mt-1">
                                    レポート:
                                    <?php echo e($recommendation->insight->analysisReport->adAccount->account_name ?? 'レポート'); ?>

                                </p>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                    
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="font-bold text-lg flex-1 text-gray-900">
                            <?php echo e($recommendation->title); ?>

                        </h3>

                        <?php
                            $statusConfig = match ($recommendation->status->value) {
                                'pending' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => '未着手'],
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
                                'dismissed' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => '却下'],
                            };
                        ?>
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold <?php echo e($statusConfig['bg']); ?> <?php echo e($statusConfig['text']); ?>">
                            <?php echo e($statusConfig['label']); ?>

                        </span>
                    </div>

                    
                    <p class="text-sm text-gray-600 line-clamp-3">
                        <?php echo e($recommendation->description); ?>

                    </p>

                    
                    <div class="flex flex-wrap items-center gap-4 text-sm">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span class="text-gray-500">難易度:</span>
                            <span class="font-semibold text-gray-900">
                                <?php echo e(match ($recommendation->implementation_difficulty) {
                                    'easy' => '簡単',
                                    'medium' => '普通',
                                    'hard' => '難しい',
                                }); ?>

                            </span>
                        </div>

                        <!--[if BLOCK]><![endif]--><?php if($recommendation->estimated_impact): ?>
                            <div class="flex items-start gap-2 w-full">
                                <svg class="w-4 h-4 text-green-600 flex-shrink-0 mt-0.5" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                                <div class="flex-1 min-w-0">
                                    <span class="text-gray-500 text-sm">効果:</span>
                                    <?php
                                        // estimated_impactを | で分割して、最初の1行だけ表示
                                        $impactLines = explode(' | ', $recommendation->estimated_impact);
                                        $firstLine = $impactLines[0] ?? $recommendation->estimated_impact;
                                    ?>
                                    <p class="font-semibold text-gray-900 text-sm mt-1 line-clamp-2"><?php echo e($firstLine); ?>

                                    </p>
                                    <!--[if BLOCK]><![endif]--><?php if(count($impactLines) > 1): ?>
                                        <p class="text-xs text-gray-500 mt-1">+<?php echo e(count($impactLines) - 1); ?>件の詳細あり</p>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    
                    <div class="flex gap-2 pt-4 border-t border-gray-200">
                        <a href="/recommendations/<?php echo e($recommendation->id); ?>"
                            class="btn btn-primary text-sm flex-1 justify-center" style="color: #000000 !important;">
                            詳細を見る
                        </a>
                    </div>
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
                                    d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">改善施策がありません</h3>
                        <p class="text-gray-500 mb-6">レポートを生成して、AIによる提案を確認しましょう</p>
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

    
    <!--[if BLOCK]><![endif]--><?php if($recommendations->hasPages()): ?>
        <div class="card p-4">
            <?php echo e($recommendations->links()); ?>

        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div><?php /**PATH /var/www/html/resources/views/livewire/recommendations/recommendation-list.blade.php ENDPATH**/ ?>