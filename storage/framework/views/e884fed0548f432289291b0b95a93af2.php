<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

?>

<div class="h-full flex flex-col animate-fade-in" style="color: #ffffff; background-color: #000;">
    
    <div class="flex items-center justify-between border-b px-6 py-4" style="border-color: #2a2d2e;">
        <h1 class="text-2xl font-semibold" style="color: #fff;">環境設定</h1>
        <a href="/dashboard" class="p-2 hover:bg-gray-800 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </a>
    </div>

    <div class="flex flex-1 overflow-hidden">
        
        <nav class="w-64 border-r p-4 space-y-1 overflow-y-auto"
            style="border-color: #2a2d2e; background-color: #1a1d21;">
            <a href="/settings/profile"
                class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors <?php echo e(request()->routeIs('profile.edit') ? 'bg-white/10' : 'hover:bg-white/5'); ?>"
                style="color: #fff;">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                プロフィール
            </a>
            <a href="/settings/password"
                class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors <?php echo e(request()->routeIs('user-password.edit') ? 'bg-white/10' : 'hover:bg-white/5'); ?>"
                style="color: #fff;">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
                パスワード
            </a>
            <a href="/settings/appearance"
                class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors <?php echo e(request()->routeIs('appearance.edit') ? 'bg-white/10' : 'hover:bg-white/5'); ?>"
                style="color: #fff;">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
                表示
            </a>
        </nav>

        
        <div class="flex-1 overflow-y-auto p-8">
            
            <div>
                <h2 class="text-2xl font-bold mb-4" style="color: #ffffff;">パスワード変更</h2>
                <p class="mb-6" style="color: #9ca3af;">安全性の高いパスワードを設定してください</p>

                
                <!--[if BLOCK]><![endif]--><?php if(session('message')): ?>
                    <div class="p-4 bg-green-100 border-l-4 border-green-500 rounded-lg mb-6">
                        <div class="flex items-center gap-2 text-green-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
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
                            <input id="password" type="password" wire:model="password" required
                                autocomplete="new-password" class="w-full px-4 py-2.5 rounded-lg transition-colors"
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
                            <label for="password_confirmation" class="block text-sm font-bold mb-2"
                                style="color: #ffffff;">
                                新しいパスワード（確認）
                            </label>
                            <input id="password_confirmation" type="password" wire:model="password_confirmation"
                                required autocomplete="new-password"
                                class="w-full px-4 py-2.5 rounded-lg transition-colors"
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            8文字以上の長さ
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4" style="color: #60a5fa;" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
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
    </div>
</div><?php /**PATH /var/www/html/resources/views/livewire/settings/password.blade.php ENDPATH**/ ?>