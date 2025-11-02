<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

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

            
            <div class="card p-8 mb-8">
                <h2 class="text-xl font-bold mb-4" style="color: #ffffff;">カラーモード</h2>
                <p class="mb-6 text-sm" style="color: #9ca3af;">
                    アプリケーションの画面のライトとダークを切り替えます。
                </p>

                <div class="grid grid-cols-2 gap-4">
                    
                    <label class="cursor-pointer">
                        <input type="radio" name="colorMode" value="light" wire:model="colorMode"
                            wire:change="updateColorMode" class="sr-only peer" />
                        <div class="p-6 border-2 rounded-xl text-center transition-all hover:border-blue-400 peer-checked:border-blue-500 peer-checked:bg-blue-500/10"
                            style="border-color: #374151; background-color: rgba(255, 255, 255, 0.05);">
                            <div class="flex justify-center mb-3">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <span class="font-semibold" style="color: #ffffff;">ライト</span>
                        </div>
                    </label>

                    
                    <label class="cursor-pointer">
                        <input type="radio" name="colorMode" value="dark" wire:model.live="colorMode"
                            wire:change="updateColorMode" class="sr-only peer" />
                        <div class="p-6 border-2 rounded-xl text-center transition-all hover:border-blue-400 peer-checked:border-blue-500 peer-checked:bg-blue-500/10"
                            style="border-color: #374151; background-color: rgba(255, 255, 255, 0.05);">
                            <div class="flex justify-center mb-3">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                </svg>
                            </div>
                            <span class="font-semibold" style="color: #ffffff;">ダーク</span>
                        </div>
                    </label>
                </div>
            </div>

            
            <!--[if BLOCK]><![endif]--><?php if($colorMode === 'dark'): ?>
                <div class="card p-8 mb-8">
                    <div class="flex gap-4 mb-6 border-b" style="border-color: #374151;">
                        <button class="pb-4 px-2 font-semibold border-b-2 transition-colors"
                            style="color: #3b82f6; border-color: #3b82f6;">
                            Slack テーマ
                        </button>
                        <button class="pb-4 px-2 font-semibold transition-colors"
                            style="color: #9ca3af; border-color: transparent;">
                            カスタムテーマ
                        </button>
                    </div>

                    
                    <div class="mb-8">
                        <h3 class="text-lg font-bold mb-4" style="color: #ffffff;">単色</h3>
                        <div class="space-y-3">
                            <?php
                                $themes = [
                                    'aubergine' => [
                                        'name' => 'Aubergine',
                                        'gradient' => 'from-purple-900 via-purple-800 to-purple-900',
                                    ],
                                    'clementine' => [
                                        'name' => 'Clementine',
                                        'gradient' => 'from-orange-700 via-orange-600 to-orange-700',
                                    ],
                                    'banana' => [
                                        'name' => 'Banana',
                                        'gradient' => 'from-yellow-600 via-yellow-500 to-yellow-600',
                                    ],
                                    'jade' => [
                                        'name' => 'Jade',
                                        'gradient' => 'from-teal-900 via-teal-800 to-teal-900',
                                    ],
                                    'lagoon' => [
                                        'name' => 'Lagoon',
                                        'gradient' => 'from-blue-900 via-blue-800 to-blue-900',
                                    ],
                                    'barbra' => [
                                        'name' => 'Barbra',
                                        'gradient' => 'from-red-900 via-red-800 to-red-900',
                                    ],
                                    'gray' => [
                                        'name' => 'Gray',
                                        'gradient' => 'from-gray-700 via-gray-600 to-gray-700',
                                    ],
                                    'mood-indigo' => [
                                        'name' => 'Mood Indigo',
                                        'gradient' => 'from-indigo-900 via-indigo-800 to-indigo-900',
                                    ],
                                ];
                            ?>
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $themes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $theme): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label class="cursor-pointer block">
                                    <input type="radio" name="slackTheme" value="<?php echo e($key); ?>"
                                        wire:model="slackTheme" wire:change="updateSlackTheme"
                                        class="sr-only peer" />
                                    <div class="p-4 rounded-lg border-2 transition-all hover:border-blue-400 peer-checked:border-blue-500 peer-checked:ring-2 peer-checked:ring-blue-500"
                                        style="border-color: #374151; background-color: rgba(255, 255, 255, 0.05);">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-12 h-12 rounded-lg flex-shrink-0 bg-gradient-to-br <?php echo e($theme['gradient']); ?>">
                                            </div>
                                            <span class="font-semibold"
                                                style="color: #ffffff;"><?php echo e($theme['name']); ?></span>
                                        </div>
                                    </div>
                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>

                    
                    <div class="mb-8">
                        <h3 class="text-lg font-bold mb-4" style="color: #ffffff;">見やすい配色 (視覚補助)</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="accessibilityTheme" value="tritanopia"
                                    wire:model="accessibilityTheme" wire:change="updateAccessibilityTheme"
                                    class="sr-only peer" />
                                <div class="p-4 rounded-lg border-2 transition-all hover:border-blue-400 peer-checked:border-blue-500 peer-checked:ring-2 peer-checked:ring-blue-500"
                                    style="border-color: #374151; background-color: rgba(255, 255, 255, 0.05);">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-12 h-12 rounded-lg flex-shrink-0 bg-gradient-to-br from-gray-900 via-gray-700 to-gray-900">
                                        </div>
                                        <div>
                                            <span class="font-semibold block"
                                                style="color: #ffffff;">Tritanopia</span>
                                            <span class="text-xs" style="color: #9ca3af;">色覚異常に対応</span>
                                        </div>
                                    </div>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="accessibilityTheme" value="protanopia-deuteranopia"
                                    wire:model="accessibilityTheme" wire:change="updateAccessibilityTheme"
                                    class="sr-only peer" />
                                <div class="p-4 rounded-lg border-2 transition-all hover:border-blue-400 peer-checked:border-blue-500 peer-checked:ring-2 peer-checked:ring-blue-500"
                                    style="border-color: #374151; background-color: rgba(255, 255, 255, 0.05);">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-12 h-12 rounded-lg flex-shrink-0 bg-gradient-to-br from-purple-900 via-purple-700 to-purple-900">
                                        </div>
                                        <div>
                                            <span class="font-semibold block" style="color: #ffffff;">Protanopia &
                                                Deuteranopia</span>
                                            <span class="text-xs" style="color: #9ca3af;">色覚異常に対応</span>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>
    </div>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('theme-updated', () => {
            // ページをリロードしてテーマを適用
            setTimeout(() => {
                window.location.reload();
            }, 300);
        });
    });
</script><?php /**PATH /var/www/html/resources/views/livewire/settings/appearance.blade.php ENDPATH**/ ?>