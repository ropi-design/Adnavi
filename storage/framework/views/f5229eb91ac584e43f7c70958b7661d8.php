<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\WithFileUploads;
use Livewire\Volt\Component;

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
            <h2 class="text-2xl font-bold mb-4" style="color: #ffffff;">プロフィール設定</h2>
            <p class="mb-6" style="color: #9ca3af;">名前とメールアドレスを更新できます</p>

            
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
                <form wire:submit="updateProfileInformation" class="space-y-6">
                    
                    <div>
                        <label class="block text-sm font-bold mb-2" style="color: #ffffff;">
                            ユーザーアイコン
                        </label>
                        <div class="flex items-center gap-6" x-data="{ preview: null }"
                            @avatar-preview-updated.window="preview = $event.detail">
                            
                            <div class="flex-shrink-0">
                                <div x-show="!preview">
                                    <!--[if BLOCK]><![endif]--><?php if(auth()->user()->avatar): ?>
                                        <img src="<?php echo e(auth()->user()->avatar_url); ?>?v=<?php echo e(time()); ?>" alt="Avatar"
                                            class="w-24 h-24 rounded-lg object-cover border-2"
                                            style="border-color: #e5e7eb;"
                                            onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="w-24 h-24 rounded-lg flex items-center justify-center text-2xl font-bold border-2 hidden"
                                            style="background-color: #9ca3af; color: #ffffff; border-color: #e5e7eb;">
                                            <?php echo e(auth()->user()->initials()); ?>

                                        </div>
                                    <?php else: ?>
                                        <div class="w-24 h-24 rounded-lg flex items-center justify-center text-2xl font-bold border-2"
                                            style="background-color: #9ca3af; color: #ffffff; border-color: #e5e7eb;">
                                            <?php echo e(auth()->user()->initials()); ?>

                                        </div>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                <img x-show="preview" :src="preview" alt="Avatar Preview"
                                    class="w-24 h-24 rounded-lg object-cover border-2" style="border-color: #e5e7eb;">
                            </div>

                            
                            <div class="flex-1 space-y-3">
                                <div>
                                    <label for="avatar-upload" class="inline-block cursor-pointer">
                                        <input type="file" id="avatar-upload" wire:model="avatar" accept="image/*"
                                            class="hidden"
                                            x-on:change="
                                            const file = $event.target.files[0];
                                            if (file) {
                                                const reader = new FileReader();
                                                reader.onload = (e) => {
                                                    window.dispatchEvent(new CustomEvent('avatar-preview-updated', { detail: e.target.result }));
                                                };
                                                reader.readAsDataURL(file);
                                            }
                                        ">
                                        <span
                                            class="inline-flex items-center px-4 py-2 rounded-lg font-semibold text-sm transition-colors border-2"
                                            style="background-color: #1e40af; color: #ffffff; border-color: #1e3a8a;">
                                            <!--[if BLOCK]><![endif]--><?php if($avatar): ?>
                                                ファイルが選択されました
                                            <?php else: ?>
                                                選択されていません
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        </span>
                                    </label>
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['avatar'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                    <p class="mt-1 text-xs" style="color: #9ca3af;">
                                        JPG, PNG, GIF形式、最大10MBまで（自動リサイズ: 800x800px）
                                    </p>
                                </div>

                                <!--[if BLOCK]><![endif]--><?php if($avatarPreview || auth()->user()->avatar): ?>
                                    <button type="button" wire:click="removeAvatar"
                                        class="text-sm text-red-600 hover:text-red-700 font-semibold">
                                        アイコンを削除
                                    </button>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        </div>
                    </div>

                    
                    <div>
                        <label for="name" class="block text-sm font-bold mb-2" style="color: #ffffff;">
                            名前
                        </label>
                        <input id="name" type="text" wire:model="name" required autofocus autocomplete="name"
                            class="w-full px-4 py-2.5 rounded-lg transition-colors"
                            style="background-color: #ffffff; color: #000000; border: 2px solid #e5e7eb;" />
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    
                    <div>
                        <label for="email" class="block text-sm font-bold mb-2" style="color: #ffffff;">
                            メールアドレス
                        </label>
                        <input id="email" type="email" wire:model="email" required autocomplete="email"
                            class="w-full px-4 py-2.5 rounded-lg transition-colors"
                            style="background-color: #ffffff; color: #000000; border: 2px solid #e5e7eb;" />
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->

                        
                        <?php if(auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !auth()->user()->hasVerifiedEmail()): ?>
                            <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <p class="text-sm text-yellow-800 mb-2">
                                    メールアドレスが未確認です。
                                </p>
                                <button type="button" wire:click="resendVerificationNotification"
                                    class="text-sm text-blue-600 hover:text-blue-700 font-semibold underline">
                                    確認メールを再送信
                                </button>

                                <!--[if BLOCK]><![endif]--><?php if(session('status') === 'verification-link-sent'): ?>
                                    <p class="mt-2 text-sm text-green-600 font-medium">
                                        新しい確認リンクをメールアドレスに送信しました。
                                    </p>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    
                    <div class="flex items-center gap-4 pt-4 border-t border-gray-200">
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-colors border-2"
                            style="background-color: #ffffff; color: #000000; border-color: #e5e7eb;"
                            data-test="update-profile-button">
                            保存
                        </button>
                    </div>
                </form>
            </div>

            
            <div class="card p-8 mt-8 border-red-200">
                <h2 class="text-xl font-bold mb-4" style="color: #ffffff;">アカウント削除</h2>
                <p class="mb-6" style="color: #ffffff;">
                    アカウントを削除すると、すべてのデータが完全に削除されます。この操作は取り消せません。
                </p>
                <button
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-colors border-2"
                    style="background-color: #dc2626; color: #ffffff; border-color: #b91c1c;"
                    onclick="alert('この機能は実装中です')">
                    アカウントを削除
                </button>
            </div>
        </div>
    </div>
</div><?php /**PATH /var/www/html/resources/views/livewire/settings/profile.blade.php ENDPATH**/ ?>