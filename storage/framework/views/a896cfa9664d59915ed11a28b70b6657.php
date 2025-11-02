<?php

use App\Models\Insight;
use App\Services\AI\GeminiService;
use Illuminate\Support\Facades\Auth;

?>

<div class="p-6 lg:p-8 space-y-6 animate-fade-in">
    
    <div wire:loading wire:target="loadInsight" class="flex flex-col items-center justify-center py-16">
        <svg class="w-12 h-12 text-blue-600 animate-spin mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        <p class="text-gray-600 font-medium">インサイトを読み込んでいます...</p>
    </div>

    <!--[if BLOCK]><![endif]--><?php if($insight && !$loading): ?>
        
        <div class="mb-4">
            <div class="flex gap-3">
                <a href="/insights"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-colors border-2"
                    style="background-color: #ffffff; color: #000000; border-color: #e5e7eb;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    インサイト一覧に戻る
                </a>
                <a href="/reports/<?php echo e($insight->analysisReport->id); ?>"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-colors border-2"
                    style="background-color: #1e40af; color: #ffffff; border-color: #1e3a8a;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    レポート詳細を見る
                </a>
            </div>
        </div>

        
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-4">
                    <?php
                        $priorityConfig = match ($insight->priority->value) {
                            'high' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => '高'],
                            'medium' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'label' => '中'],
                            'low' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => '低'],
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
                        class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-bold <?php echo e($priorityConfig['bg']); ?> <?php echo e($priorityConfig['text']); ?>">
                        優先度: <?php echo e($priorityConfig['label']); ?>

                    </span>
                    <span
                        class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-purple-100 text-purple-800">
                        <?php echo e($categoryLabel); ?>

                    </span>
                </div>

                <h1 class="text-4xl font-bold mb-4" style="color: #ffffff;"><?php echo e($insight->title); ?></h1>
                <p class="text-lg leading-relaxed" style="color: #ffffff;"><?php echo e($insight->description); ?></p>

                <div class="flex flex-wrap items-start gap-6 mt-6">
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <div class="flex items-baseline gap-2">
                                <span style="color: #ffffff;">インパクト:</span>
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
                                    class="inline-flex items-center px-3 py-1 rounded-full text-base font-bold <?php echo e($impactLabel['bg']); ?> <?php echo e($impactLabel['text']); ?>">
                                    <?php echo e($impactLabel['label']); ?>

                                </span>
                            </div>
                        </div>
                        <p class="text-xs" style="color: #ffffff; opacity: 0.7; max-w-xs;">
                            このインサイトがビジネスに与える影響の大きさ。改善した場合の効果の大きさを示します。
                        </p>
                    </div>
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="flex items-baseline gap-2">
                                <span style="color: #ffffff;">信頼度:</span>
                                <span class="text-2xl font-bold"
                                    style="color: #ffffff;"><?php echo e(number_format($insight->confidence_score * 100)); ?>%</span>
                            </div>
                        </div>
                        <p class="text-xs" style="color: #ffffff; opacity: 0.7; max-w-xs;">
                            このインサイトの分析結果の信頼性（0-100%）。数字が大きいほど、データに基づいた確かな分析であることを示します。
                        </p>
                    </div>
                </div>
            </div>

            <a href="/reports/<?php echo e($insight->analysisReport->id); ?>"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-colors border-2"
                style="background-color: #ffffff; color: #000000; border-color: #e5e7eb;">
                レポートを見る
            </a>
        </div>

        
        <!--[if BLOCK]><![endif]--><?php if(session('message')): ?>
            <div class="p-4 bg-green-100 border-l-4 border-green-500 rounded-lg">
                <div class="flex items-center gap-2 text-green-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <?php echo e(session('message')); ?>

                </div>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        
        <!--[if BLOCK]><![endif]--><?php if($insight->data_points): ?>
            <?php
                $dataPoints = is_array($insight->data_points)
                    ? $insight->data_points
                    : json_decode($insight->data_points, true);
            ?>
            <!--[if BLOCK]><![endif]--><?php if($dataPoints): ?>
                <div class="card p-6">
                    <h2 class="text-2xl font-bold mb-4" style="color: #ffffff;">データポイント</h2>
                    <div class="p-4 rounded-lg" style="background-color: rgba(255, 255, 255, 0.1);">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <!--[if BLOCK]><![endif]--><?php if(isset($dataPoints['current_value'])): ?>
                                <div>
                                    <span class="text-white" style="opacity: 0.8;">現在の値:</span>
                                    <span class="font-bold ml-2 text-white">
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
                                    <span class="text-white" style="opacity: 0.8;">目標値:</span>
                                    <span class="font-bold ml-2" style="color: #10b981;">
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
                                    <span class="text-white" style="opacity: 0.8;">ベンチマーク:</span>
                                    <span class="font-bold ml-2 text-white" style="opacity: 0.7;">
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
                                    <span class="text-white" style="opacity: 0.8;">影響を受ける指標:</span>
                                    <div class="flex flex-wrap gap-2 mt-2">
                                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $dataPoints['affected_metrics']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metric): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold"
                                                style="background-color: rgba(59, 130, 246, 0.3); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.5);">
                                                <?php echo e($metric); ?>

                                            </span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                </div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        
        <!--[if BLOCK]><![endif]--><?php if($insight->recommendations->count() > 0): ?>
            <div class="card p-6" id="recommendations">
                <h2 class="text-2xl font-bold mb-6" style="color: #ffffff;">関連する改善施策</h2>

                <div class="space-y-4">
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $insight->recommendations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $recommendation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="p-6 border-2 rounded-xl transition-all"
                            style="background-color: #ffffff; border-color: #e5e7eb;">
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
                                        ?>
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold <?php echo e($statusConfig['bg']); ?> <?php echo e($statusConfig['text']); ?>">
                                            <?php echo e($statusConfig['label']); ?>

                                        </span>
                                    </div>

                                    <h4 class="font-bold text-xl mb-2" style="color: #000000;">
                                        <?php echo e($recommendation->title); ?></h4>
                                    <p class="mb-4" style="color: #000000;"><?php echo e($recommendation->description); ?></p>

                                    <div class="flex items-center gap-6 text-sm">
                                        <div>
                                            <span style="color: #000000;">難易度:</span>
                                            <span class="font-semibold" style="color: #000000;">
                                                <?php echo e(match ($recommendation->implementation_difficulty) {
                                                    'easy' => '簡単',
                                                    'medium' => '普通',
                                                    'hard' => '難しい',
                                                }); ?>

                                            </span>
                                        </div>
                                        <!--[if BLOCK]><![endif]--><?php if($recommendation->estimated_impact): ?>
                                            <div>
                                                <span style="color: #000000;">推定効果:</span>
                                                <span class="font-semibold"
                                                    style="color: #000000;"><?php echo e($recommendation->estimated_impact); ?></span>
                                            </div>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>

                                    <!--[if BLOCK]><![endif]--><?php if($recommendation->specific_actions && count($recommendation->specific_actions) > 0): ?>
                                        <div class="mt-4">
                                            <p class="text-sm font-semibold mb-2" style="color: #000000;">実施手順:</p>
                                            <ul class="space-y-2">
                                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $recommendation->specific_actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <li class="flex items-start gap-2 text-sm"
                                                        style="color: #000000;">
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

                                <div class="flex flex-col gap-2">
                                    <a href="/recommendations/<?php echo e($recommendation->id); ?>"
                                        class="btn btn-primary text-sm">
                                        詳細
                                    </a>
                                    <!--[if BLOCK]><![endif]--><?php if($recommendation->status->value === 'pending'): ?>
                                        <button wire:click="implementRecommendation(<?php echo e($recommendation->id); ?>)"
                                            class="btn btn-success text-sm">
                                            実施済みにする
                                        </button>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        
        <div class="card p-6">
            <h2 class="text-2xl font-bold mb-4" style="color: #ffffff;">メタ情報</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span style="color: #ffffff;">レポート:</span>
                    <span class="font-semibold"
                        style="color: #ffffff;"><?php echo e($insight->analysisReport->adAccount->account_name); ?></span>
                </div>
                <div>
                    <span style="color: #ffffff;">作成日:</span>
                    <span class="font-semibold"
                        style="color: #ffffff;"><?php echo e($insight->created_at->isoFormat('YYYY年MM月DD日 HH:mm')); ?></span>
                </div>
            </div>
        </div>

        
        <div class="card p-6">
            <h2 class="text-2xl font-bold mb-4" style="color: #ffffff;">
                <svg class="w-6 h-6 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
                AIに質問する
            </h2>
            <p class="text-sm mb-4" style="color: #ffffff; opacity: 0.8;">
                このインサイトについて、Gemini AIに直接質問できます。原因分析や改善方法について詳しく知りたい場合は質問してください。
            </p>

            <form wire:submit="askQuestion" class="space-y-4">
                <div>
                    <textarea wire:model="question" rows="3"
                        placeholder="例: この問題の根本原因について詳しく教えてください&#10;例: 改善するためにどのようなアクションを取ればいいですか？"
                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 focus:border-blue-500 focus:outline-none resize-none"
                        style="background-color: #ffffff; color: #000000;" wire:loading.attr="disabled"></textarea>
                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['question'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                </div>

                <div class="flex gap-3">
                    <button type="submit" wire:loading.attr="disabled"
                        class="px-6 py-3 rounded-lg font-semibold transition-all flex items-center gap-2"
                        style="background-color: #667eea; color: #ffffff;" wire:loading.class="opacity-50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            wire:loading.class="animate-spin" wire:target="askQuestion">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span wire:loading.remove wire:target="askQuestion">質問を送信</span>
                        <span wire:loading wire:target="askQuestion">回答を生成中...</span>
                    </button>
                    <!--[if BLOCK]><![endif]--><?php if($answer): ?>
                        <button type="button" wire:click="$set('question', ''); $set('answer', null);"
                            class="px-6 py-3 rounded-lg font-semibold transition-all"
                            style="background-color: #e5e7eb; color: #000000;">
                            クリア
                        </button>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>

                <!--[if BLOCK]><![endif]--><?php if($error): ?>
                    <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                        <div class="flex items-center gap-2 text-red-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <?php echo e($error); ?>

                        </div>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                <!--[if BLOCK]><![endif]--><?php if($answer): ?>
                    <div class="p-6 bg-blue-50 border-2 border-blue-200 rounded-lg">
                        <div class="flex items-start gap-3 mb-3">
                            <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-1" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M6.343 6.343l-.707.707m12.728 0l-.707.707M6.343 17.657l-.707.707M17.657 6.343l-.707-.707m-12.728 0l-.707.707m12.728 12.728l-.707.707M17.657 17.657l-.707-.707M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="text-lg font-bold" style="color: #1e40af;">AI回答</h3>
                        </div>
                        <div class="prose max-w-none" style="color: #1e3a8a;">
                            <p class="whitespace-pre-wrap leading-relaxed"><?php echo e($answer); ?></p>
                        </div>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </form>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div><?php /**PATH /var/www/html/resources/views/livewire/insights/insight-detail.blade.php ENDPATH**/ ?>