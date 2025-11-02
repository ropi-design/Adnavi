<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

?>

<div class="p-6 lg:p-8 space-y-8 animate-fade-in" style="color: #ffffff;">
    <div class="max-w-7xl mx-auto">
        
        <div class="mb-8">
            <h1 class="text-3xl font-bold" style="color: #ffffff;">環境設定</h1>
            <p class="mt-2" style="color: #9ca3af;">アプリケーションの設定を管理できます</p>
        </div>

        
        <div class="mb-8">
            <nav class="flex gap-4 border-b" style="border-color: #374151;">
                <a href="/settings/profile"
                    class="pb-4 px-2 font-semibold transition-colors border-b-2 <?php echo e(request()->routeIs('profile.edit') ? 'text-blue-500 border-blue-500' : 'text-gray-400 border-transparent hover:text-gray-300'); ?>"
                    style="<?php echo e(request()->routeIs('profile.edit') ? 'color: #3b82f6; border-color: #3b82f6;' : 'color: #9ca3af; border-color: transparent;'); ?>">
                    プロフィール
                </a>
                <a href="/settings/password"
                    class="pb-4 px-2 font-semibold transition-colors border-b-2 <?php echo e(request()->routeIs('user-password.edit') ? 'text-blue-500 border-blue-500' : 'text-gray-400 border-transparent hover:text-gray-300'); ?>"
                    style="<?php echo e(request()->routeIs('user-password.edit') ? 'color: #3b82f6; border-color: #3b82f6;' : 'color: #9ca3af; border-color: transparent;'); ?>">
                    パスワード
                </a>
                <a href="/settings/appearance"
                    class="pb-4 px-2 font-semibold transition-colors border-b-2 <?php echo e(request()->routeIs('appearance.edit') ? 'text-blue-500 border-blue-500' : 'text-gray-400 border-transparent hover:text-gray-300'); ?>"
                    style="<?php echo e(request()->routeIs('appearance.edit') ? 'color: #3b82f6; border-color: #3b82f6;' : 'color: #9ca3af; border-color: transparent;'); ?>">
                    表示
                </a>
            </nav>
        </div>

        
        <div>
            <h2 class="text-2xl font-bold mb-4" style="color: #ffffff;">パスワード変更</h2>
            <p class="mb-6" style="color: #9ca3af;">安全性の高いパスワードを設定してください</p>

            
            <!--[if BLOCK]><![endif]--><?php if(session('message')): ?>
                <div class="p-4 bg-green-100 border-l-4 border-green-500 rounded-lg mb-6">
                    <div class="flex items-center gap-2 text-green-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <?php echo e(session('message')); ?>

                    </div>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            
            <div class="card p-8">
                <form wire:submit="updatePassword" class="space-y-6">
                    
                    <div>
                        <label for="current_password" class="block text-sm font-bold mb-2" style="color: #ffffff;">
                            現在のパスワード
                        </label>
                        <input id="current_password" type="password" wire:model="current_password" required
                            autocomplete="current-password" class="w-full px-4 py-2.5 rounded-lg transition-colors"
                            style="background-color: #ffffff; color: #000000; border: 2px solid #e5e7eb;" />
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm" style="color: #ef4444;"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    
                    <div>
                        <label for="password" class="block text-sm font-bold mb-2" style="color: #ffffff;">
                            新しいパスワード
                        </label>
                        <input id="password" type="password" wire:model="password" required autocomplete="new-password"
                            class="w-full px-4 py-2.5 rounded-lg transition-colors"
                            style="background-color: #ffffff; color: #000000; border: 2px solid #e5e7eb;" />
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm" style="color: #ef4444;"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        <p class="mt-1 text-sm" style="color: #9ca3af;">8文字以上で設定してください</p>
                    </div>

                    
                    <div>
                        <label for="password_confirmation" class="block text-sm font-bold mb-2" style="color: #ffffff;">
                            新しいパスワード（確認）
                        </label>
                        <input id="password_confirmation" type="password" wire:model="password_confirmation" required
                            autocomplete="new-password" class="w-full px-4 py-2.5 rounded-lg transition-colors"
                            style="background-color: #ffffff; color: #000000; border: 2px solid #e5e7eb;" />
                    </div>

                    
                    <div class="flex items-center gap-4 pt-4 border-t" style="border-color: #374151;">
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-colors border-2"
                            style="background-color: #ffffff; color: #000000; border-color: #e5e7eb;"
                            data-test="update-password-button">
                            パスワードを更新
                        </button>
                    </div>
                </form>
            </div>

            
            <div class="card p-6 mt-8" style="background-color: rgba(59, 130, 246, 0.1); border-color: #3b82f6;">
                <h3 class="text-lg font-bold mb-3" style="color: #ffffff;">パスワードのヒント</h3>
                <ul class="space-y-2 text-sm" style="color: #ffffff;">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4" style="color: #60a5fa;" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        8文字以上の長さ
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4" style="color: #60a5fa;" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        大文字と小文字を組み合わせる
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4" style="color: #60a5fa;" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7" />
                        </svg>
                        数字と記号を含める
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div><?php /**PATH /var/www/html/resources/views/livewire/settings/password.blade.php ENDPATH**/ ?>