<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use function Livewire\Volt\{state, mount};

state([
    'colorMode' => 'dark', // light, dark, system
    'slackTheme' => 'dark', // dark, aubergine, clementine, banana, jade, lagoon, barbra, gray, mood-indigo
    'accessibilityTheme' => null, // tritanopia, protanopia-deuteranopia
]);

mount(function () {
    $user = Auth::user();

    // themeカラムが存在するか確認
    $theme = 'dark';
    if (Schema::hasColumn('users', 'theme')) {
        $theme = $user->theme ?? 'dark';
    }

    // テーマをカラーモードとSlackテーマに分離
    // theme形式: "dark" または "dark-aubergine" または "dark-tritanopia" のような形式
    if (str_contains($theme, '-')) {
        $parts = explode('-', $theme, 2);
        $this->colorMode = $parts[0];
        $themeValue = $parts[1] ?? 'dark';

        // アクセシビリティテーマかどうか判定
        if (in_array($themeValue, ['tritanopia', 'protanopia-deuteranopia'])) {
            $this->accessibilityTheme = $themeValue;
            $this->slackTheme = 'dark'; // デフォルトのテーマ
        } else {
            $this->slackTheme = $themeValue;
            $this->accessibilityTheme = null;
        }
    } else {
        $this->colorMode = in_array($theme, ['light', 'dark', 'system']) ? $theme : 'dark';
        if (!in_array($theme, ['light', 'dark', 'system'])) {
            $this->slackTheme = $theme;
        }
        $this->accessibilityTheme = null;
    }
});

$saveTheme = function () {
    $user = Auth::user();

    // themeカラムが存在する場合のみ保存
    if (!Schema::hasColumn('users', 'theme')) {
        return;
    }

    // テーマを結合（例: "dark-aubergine"）
    if ($this->accessibilityTheme) {
        $theme = $this->colorMode . '-' . $this->accessibilityTheme;
    } else {
        if ($this->colorMode === 'system') {
            $theme = 'system';
        } else {
            $theme = $this->colorMode . '-' . $this->slackTheme;
        }
    }

    $user->theme = $theme;
    $user->save();
};

$updateColorMode = function () {
    $this->saveTheme();
    $user = Auth::user();

    // 保存されたテーマを取得
    $theme = $user->theme ?? 'dark';
    session()->flash('message', 'カラーモードを更新しました');
    $this->dispatch('theme-updated', theme: $theme);
};

$updateSlackTheme = function () {
    $this->accessibilityTheme = null; // 単色テーマ選択時はアクセシビリティテーマをリセット
    $this->saveTheme();
    $user = Auth::user();

    // 保存されたテーマを取得
    $theme = $user->theme ?? 'dark';
    session()->flash('message', 'テーマを更新しました');
    $this->dispatch('theme-updated', theme: $theme);
};

$updateAccessibilityTheme = function () {
    $this->saveTheme();
    $user = Auth::user();

    // 保存されたテーマを取得
    $theme = $user->theme ?? 'dark';
    session()->flash('message', 'アクセシビリティテーマを更新しました');
    $this->dispatch('theme-updated', theme: $theme);
};

?>

<div class="h-full flex flex-col animate-fade-in bg-white dark:bg-black text-gray-900 dark:text-white"
    style="background-color: var(--theme-bg); color: var(--theme-text);">
    {{-- ヘッダー --}}
    <div class="flex items-center justify-between border-b px-6 py-4 border-gray-200 dark:border-gray-700">
        <h1 class="text-2xl font-semibold">環境設定</h1>
        <a href="/dashboard" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </a>
    </div>

    <div class="flex flex-1 overflow-hidden">
        {{-- 左サイドバーナビゲーション --}}
        <nav
            class="w-64 border-r p-4 space-y-1 overflow-y-auto border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <a href="/settings/profile"
                class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('profile.edit') ? 'bg-blue-100 dark:bg-white/10' : 'hover:bg-gray-100 dark:hover:bg-white/5' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                プロフィール
            </a>
            <a href="/settings/password"
                class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('user-password.edit') ? 'bg-blue-100 dark:bg-white/10' : 'hover:bg-gray-100 dark:hover:bg-white/5' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
                パスワード
            </a>
            <a href="/settings/appearance"
                class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('appearance.edit') ? 'bg-blue-100 dark:bg-white/10' : 'hover:bg-gray-100 dark:hover:bg-white/5' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
                表示
            </a>
        </nav>

        {{-- 右メインコンテンツ --}}
        <div class="flex-1 overflow-y-auto p-8">
            {{-- メッセージ --}}
            @if (session('message'))
                <div
                    class="p-4 bg-green-100 dark:bg-green-900/30 border-l-4 border-green-500 dark:border-green-400 rounded-lg mb-6">
                    <div class="flex items-center gap-2 text-green-800 dark:text-green-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ session('message') }}
                    </div>
                </div>
            @endif

            {{-- カラーモード --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-8 mb-8">
                <h2 class="text-xl font-bold mb-4">カラーモード</h2>
                <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">
                    アプリケーションの画面のライトとダークを切り替えます。
                </p>

                <div class="grid grid-cols-2 gap-4">
                    {{-- ライト --}}
                    <label class="cursor-pointer">
                        <input type="radio" name="colorMode" value="light" wire:model="colorMode"
                            wire:change="updateColorMode" class="sr-only peer" />
                        <div
                            class="p-6 border-2 rounded-xl text-center transition-all hover:border-blue-400 peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-500/10 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50">
                            <div class="flex justify-center mb-3">
                                <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <span class="font-semibold">ライト</span>
                        </div>
                    </label>

                    {{-- ダーク --}}
                    <label class="cursor-pointer">
                        <input type="radio" name="colorMode" value="dark" wire:model.live="colorMode"
                            wire:change="updateColorMode" class="sr-only peer" />
                        <div
                            class="p-6 border-2 rounded-xl text-center transition-all hover:border-blue-400 peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-500/10 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50">
                            <div class="flex justify-center mb-3">
                                <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                </svg>
                            </div>
                            <span class="font-semibold">ダーク</span>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Slack テーマ（ダークモード時のみ表示） --}}
            @if ($colorMode === 'dark')
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-8 mb-8">
                    <div class="flex gap-4 mb-6 border-b border-gray-200 dark:border-gray-700">
                        <button
                            class="pb-4 px-2 font-semibold border-b-2 border-blue-500 text-blue-600 dark:text-blue-400 transition-colors">
                            Slack テーマ
                        </button>
                        <button class="pb-4 px-2 font-semibold text-gray-400 dark:text-gray-500 transition-colors">
                            カスタムテーマ
                        </button>
                    </div>

                    {{-- 単色 --}}
                    <div class="mb-8">
                        <h3 class="text-lg font-bold mb-4">単色</h3>
                        <div class="space-y-3">
                            @php
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
                            @endphp
                            @foreach ($themes as $key => $theme)
                                <label class="cursor-pointer block">
                                    <input type="radio" name="slackTheme" value="{{ $key }}"
                                        wire:model="slackTheme" wire:change="updateSlackTheme"
                                        class="sr-only peer" />
                                    <div
                                        class="p-4 rounded-lg border-2 transition-all hover:border-blue-400 peer-checked:border-blue-500 peer-checked:ring-2 peer-checked:ring-blue-500 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-12 h-12 rounded-lg flex-shrink-0 bg-gradient-to-br {{ $theme['gradient'] }}">
                                            </div>
                                            <span class="font-semibold">{{ $theme['name'] }}</span>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- 見やすい配色（視覚補助） --}}
                    <div class="mb-8">
                        <h3 class="text-lg font-bold mb-4">見やすい配色 (視覚補助)</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="accessibilityTheme" value="tritanopia"
                                    wire:model="accessibilityTheme" wire:change="updateAccessibilityTheme"
                                    class="sr-only peer" />
                                <div
                                    class="p-4 rounded-lg border-2 transition-all hover:border-blue-400 peer-checked:border-blue-500 peer-checked:ring-2 peer-checked:ring-blue-500 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-12 h-12 rounded-lg flex-shrink-0 bg-gradient-to-br from-gray-900 via-gray-700 to-gray-900">
                                        </div>
                                        <div>
                                            <span class="font-semibold block">Tritanopia</span>
                                            <span class="text-xs text-gray-600 dark:text-gray-400">色覚異常に対応</span>
                                        </div>
                                    </div>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="accessibilityTheme" value="protanopia-deuteranopia"
                                    wire:model="accessibilityTheme" wire:change="updateAccessibilityTheme"
                                    class="sr-only peer" />
                                <div
                                    class="p-4 rounded-lg border-2 transition-all hover:border-blue-400 peer-checked:border-blue-500 peer-checked:ring-2 peer-checked:ring-blue-500 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-12 h-12 rounded-lg flex-shrink-0 bg-gradient-to-br from-purple-900 via-purple-700 to-purple-900">
                                        </div>
                                        <div>
                                            <span class="font-semibold block">Protanopia &
                                                Deuteranopia</span>
                                            <span class="text-xs text-gray-600 dark:text-gray-400">色覚異常に対応</span>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('theme-updated', (data) => {
            // テーマを即座に適用（ページリロードなし）
            const html = document.documentElement;
            const userTheme = data.theme || '{{ auth()->user()->theme ?? 'dark' }}';
            const themeParts = userTheme.split('-');
            const colorMode = themeParts[0] || 'dark';
            const themeName = themeParts[1] || 'dark';

            // 既存のテーマクラスを削除
            html.classList.remove('dark', 'theme-aubergine', 'theme-clementine', 'theme-banana',
                'theme-jade', 'theme-lagoon', 'theme-barbra', 'theme-gray', 'theme-mood-indigo',
                'theme-tritanopia', 'theme-protanopia-deuteranopia');

            // カラーモードを適用
            if (colorMode === 'dark') {
                html.classList.add('dark');
                // ダークモードでテーマ名がある場合は追加
                if (themeName && themeName !== 'dark') {
                    html.classList.add('theme-' + themeName);
                }
            } else if (colorMode === 'light') {
                html.classList.remove('dark');
            }

            // 少し遅延させてCSS変数が適用されるのを待つ
            setTimeout(() => {
                // ページ全体の背景色を更新
                const bgColor = getComputedStyle(html).getPropertyValue('--theme-bg').trim();
                if (bgColor) {
                    document.body.style.backgroundColor = bgColor;
                } else {
                    document.body.style.backgroundColor = colorMode === 'dark' ? '#000000' :
                        '#ffffff';
                }
            }, 50);
        });
    });
</script>
