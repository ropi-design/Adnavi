<?php

use App\Models\AdAccount;
use App\Models\AnalyticsProperty;
use App\Models\AnalysisReport;
use App\Models\GoogleAccount;
use App\Jobs\GenerateAnalysisReport;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\Attributes\Validate;

?>

<div class="p-6 lg:p-8 animate-fade-in">
    <div class="max-w-4xl mx-auto">
        
        <div class="mb-6">
            <div class="inline-flex items-center px-4 py-2 rounded-lg font-semibold text-lg transition-colors border-2 mb-3"
                style="background-color: #1e40af; color: #ffffff; border-color: #1e3a8a;">
                AIレポート生成
            </div>
            <p class="text-sm text-white">Geminiで効果分析を自動実行</p>
        </div>

        <form wire:submit="generate" class="card p-8 space-y-6">
            
            <div>
                <label class="block text-sm font-bold text-white mb-3">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    レポートタイプ
                </label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <label class="relative">
                        <input type="radio" wire:model.live="reportType" value="daily" class="peer sr-only" />
                        <div
                            class="p-4 border-2 rounded-xl cursor-pointer transition-all peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 border-gray-200 hover:border-blue-400 peer-checked:shadow-lg text-white">
                            <div class="text-center">
                                <div class="font-bold text-lg">日次</div>
                                <div class="text-xs mt-1">昨日のデータ</div>
                            </div>
                        </div>
                    </label>
                    <label class="relative">
                        <input type="radio" wire:model.live="reportType" value="weekly" class="peer sr-only" />
                        <div
                            class="p-4 border-2 rounded-xl cursor-pointer transition-all peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 border-gray-200 hover:border-blue-400 peer-checked:shadow-lg text-white">
                            <div class="text-center">
                                <div class="font-bold text-lg">週次</div>
                                <div class="text-xs mt-1">先週のデータ</div>
                            </div>
                        </div>
                    </label>
                    <label class="relative">
                        <input type="radio" wire:model.live="reportType" value="monthly" class="peer sr-only" />
                        <div
                            class="p-4 border-2 rounded-xl cursor-pointer transition-all peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 border-gray-200 hover:border-blue-400 peer-checked:shadow-lg text-white">
                            <div class="text-center">
                                <div class="font-bold text-lg">月次</div>
                                <div class="text-xs mt-1">先月のデータ</div>
                            </div>
                        </div>
                    </label>
                    <label class="relative">
                        <input type="radio" wire:model.live="reportType" value="custom" class="peer sr-only" />
                        <div
                            class="p-4 border-2 rounded-xl cursor-pointer transition-all peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 border-gray-200 hover:border-blue-400 peer-checked:shadow-lg text-white">
                            <div class="text-center">
                                <div class="font-bold text-lg">カスタム</div>
                                <div class="text-xs mt-1">期間を指定</div>
                            </div>
                        </div>
                    </label>
                </div>
                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['reportType'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="text-red-600 text-sm mt-1"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
            </div>

            
            <div>
                <label class="block text-sm font-bold text-white mb-2">広告アカウント *</label>
                <select wire:model="adAccountId" class="form-input"
                    style="background-color: white !important; color: #111827 !important;">
                    <option value="">選択してください</option>
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $adAccounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($account->id); ?>"><?php echo e($account->account_name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </select>
                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['adAccountId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="text-red-600 text-sm mt-1"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
            </div>

            
            <div>
                <label class="block text-sm font-bold text-white mb-2">
                    Analyticsプロパティ
                    <span class="text-xs text-white font-normal">（オプション）</span>
                </label>
                <select wire:model="analyticsPropertyId" class="form-input"
                    style="background-color: white !important; color: #111827 !important;">
                    <option value="">選択しない</option>
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $analyticsProperties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $property): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($property->id); ?>"><?php echo e($property->property_name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </select>
                <p class="text-sm text-white mt-2">Analyticsデータも含めて分析する場合は選択してください</p>
            </div>

            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-white mb-2">開始日</label>
                    <input type="date" wire:model="startDate" <?php echo e($reportType !== 'custom' ? 'disabled' : ''); ?>

                        class="form-input <?php echo e($reportType !== 'custom' ? 'opacity-50 cursor-not-allowed' : ''); ?>"
                        style="background-color: white !important; color: #111827 !important;" />
                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['startDate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-600 text-sm mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                </div>
                <div>
                    <label class="block text-sm font-bold text-white mb-2">終了日</label>
                    <input type="date" wire:model="endDate" <?php echo e($reportType !== 'custom' ? 'disabled' : ''); ?>

                        class="form-input <?php echo e($reportType !== 'custom' ? 'opacity-50 cursor-not-allowed' : ''); ?>"
                        style="background-color: white !important; color: #111827 !important;" />
                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['endDate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-600 text-sm mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>

            
            <!--[if BLOCK]><![endif]--><?php if($startDate && $endDate): ?>
                <div class="p-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-xl">
                    <div class="flex items-center gap-3 text-gray-700">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <strong>分析期間:</strong>
                            <?php echo e(\Carbon\Carbon::parse($startDate)->isoFormat('YYYY年MM月DD日')); ?>

                            〜
                            <?php echo e(\Carbon\Carbon::parse($endDate)->isoFormat('YYYY年MM月DD日')); ?>

                            <span
                                class="ml-2 text-sm">(<?php echo e(\Carbon\Carbon::parse($startDate)->diffInDays($endDate) + 1); ?>日間)</span>
                        </div>
                    </div>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            
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

            <!--[if BLOCK]><![endif]--><?php if(session('error')): ?>
                <div class="p-4 bg-red-100 border-l-4 border-red-500 rounded-lg">
                    <div class="flex items-center gap-2 text-red-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <?php echo e(session('error')); ?>

                    </div>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            
            <div class="flex gap-3 pt-4 border-t border-gray-200">
                <button type="submit" wire:loading.attr="disabled"
                    class="btn btn-primary flex items-center gap-2 flex-1 justify-center"
                    wire:loading.class="opacity-50" wire:loading.attr="disabled">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        wire:loading.class="animate-spin" wire:target="generate">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                    <span wire:loading.remove wire:target="generate">
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-md font-semibold text-sm transition-colors border"
                            style="background-color: rgba(255, 255, 255, 0.2); color: #ffffff; border-color: rgba(255, 255, 255, 0.3);">
                            AIレポート生成
                        </span>
                    </span>
                    <span wire:loading wire:target="generate">
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-md font-semibold text-sm transition-colors border"
                            style="background-color: rgba(255, 255, 255, 0.2); color: #ffffff; border-color: rgba(255, 255, 255, 0.3);">
                            生成中...
                        </span>
                    </span>
                </button>

                <a href="/reports" class="btn btn-secondary inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    キャンセル
                </a>
            </div>
        </form>
    </div>
</div><?php /**PATH /var/www/html/resources/views/livewire/reports/generate-report.blade.php ENDPATH**/ ?>