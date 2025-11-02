<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{state, mount};

state([
    'colorMode' => 'dark', // light, dark, system
    'slackTheme' => 'dark', // dark, aubergine, clementine, banana, jade, lagoon, barbra, gray, mood-indigo
    'accessibilityTheme' => null, // tritanopia, protanopia-deuteranopia
]);

mount(function () {
    $user = Auth::user();
    $theme = $user->theme ?? 'dark';

    // テーマをカラーモードとSlackテーマに分離
    // theme形式: "dark" または "dark-aubergine" のような形式
    if (str_contains($theme, '-')) {
        [$this->colorMode, $this->slackTheme] = explode('-', $theme, 2);
    } else {
        $this->colorMode = in_array($theme, ['light', 'dark', 'system']) ? $theme : 'dark';
        if (!in_array($theme, ['light', 'dark', 'system'])) {
            $this->slackTheme = $theme;
        }
    }
});

$saveTheme = function () {
    $user = Auth::user();

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
    session()->flash('message', 'カラーモードを更新しました');
    $this->dispatch('theme-updated');
};

$updateSlackTheme = function () {
    $this->accessibilityTheme = null; // 単色テーマ選択時はアクセシビリティテーマをリセット
    $this->saveTheme();
    session()->flash('message', 'テーマを更新しました');
    $this->dispatch('theme-updated');
};

$updateAccessibilityTheme = function () {
    $this->saveTheme();
    session()->flash('message', 'アクセシビリティテーマを更新しました');
    $this->dispatch('theme-updated');
};

?>

<div class="p-6 lg:p-8 space-y-8 animate-fade-in" style="color: #ffffff;">
    <div class="max-w-4xl mx-auto">
        {{-- ヘッダー --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold" style="color: #ffffff;">環境設定</h1>
            <p class="mt-2" style="color: #9ca3af;">アプリケーションの表示をカスタマイズできます</p>
        </div>

        {{-- メッセージ --}}
        @if (session('message'))
            <div class="p-4 bg-green-100 border-l-4 border-green-500 rounded-lg mb-6">
                <div class="flex items-center gap-2 text-green-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ session('message') }}
                </div>
            </div>
        @endif

        {{-- カラーモード --}}
        <div class="card p-8 mb-8">
            <h2 class="text-xl font-bold mb-4" style="color: #ffffff;">カラーモード</h2>
            <p class="mb-6 text-sm" style="color: #9ca3af;">
                アプリケーションの画面のライトとダークを切り替えます。コンピューターの設定に合わせることもできます。
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                {{-- ライト --}}
                <label class="cursor-pointer">
                    <input type="radio" name="colorMode" value="light" wire:model="colorMode"
                        wire:change="updateColorMode" class="sr-only peer" />
                    <div class="p-6 border-2 rounded-xl text-center transition-all hover:border-blue-400 peer-checked:border-blue-500 peer-checked:bg-blue-50"
                        style="border-color: #374151; background-color: rgba(255, 255, 255, 0.05);">
                        <div class="flex justify-center mb-3">
                            <svg class="w-8 h-8" style="color: #fbbf24;" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <span class="font-semibold" style="color: #ffffff;">ライト</span>
                    </div>
                </label>

                {{-- ダーク --}}
                <label class="cursor-pointer">
                    <input type="radio" name="colorMode" value="dark" wire:model.live="colorMode"
                        wire:change="updateColorMode" class="sr-only peer" />
                    <div class="p-6 border-2 rounded-xl text-center transition-all hover:border-blue-400 peer-checked:border-blue-500 peer-checked:bg-blue-50"
                        style="border-color: #374151; background-color: rgba(255, 255, 255, 0.05);">
                        <div class="flex justify-center mb-3">
                            <svg class="w-8 h-8" style="color: #6366f1;" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                        </div>
                        <span class="font-semibold" style="color: #ffffff;">ダーク</span>
                    </div>
                </label>

                {{-- システム --}}
                <label class="cursor-pointer">
                    <input type="radio" name="colorMode" value="system" wire:model.live="colorMode"
                        wire:change="updateColorMode" class="sr-only peer" />
                    <div class="p-6 border-2 rounded-xl text-center transition-all hover:border-blue-400 peer-checked:border-blue-500 peer-checked:bg-blue-50"
                        style="border-color: #374151; background-color: rgba(255, 255, 255, 0.05);">
                        <div class="flex justify-center mb-3">
                            <svg class="w-8 h-8" style="color: #6b7280;" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <span class="font-semibold" style="color: #ffffff;">システム</span>
                    </div>
                </label>
            </div>
        </div>

        {{-- Slack テーマ（ダークモード時のみ表示） --}}
        @if ($colorMode === 'dark')
            <div class="card p-8 mb-8">
                <div class="flex gap-4 mb-6 border-b" style="border-color: #374151;">
                    <button class="pb-4 px-2 font-semibold border-b-2" style="color: #3b82f6; border-color: #3b82f6;">
                        Slack テーマ
                    </button>
                    <button class="pb-4 px-2 font-semibold" style="color: #9ca3af; border-color: transparent;">
                        カスタムテーマ
                    </button>
                </div>

                {{-- 単色 --}}
                <div class="mb-8">
                    <h3 class="text-lg font-bold mb-4" style="color: #ffffff;">単色</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @php
                            $themes = [
                                'aubergine' => ['name' => 'Aubergine', 'color' => '#4a154b'],
                                'clementine' => ['name' => 'Clementine', 'color' => '#c73e1d'],
                                'banana' => ['name' => 'Banana', 'color' => '#decb00'],
                                'jade' => ['name' => 'Jade', 'color' => '#0c5449'],
                                'lagoon' => ['name' => 'Lagoon', 'color' => '#1264a3'],
                                'barbra' => ['name' => 'Barbra', 'color' => '#8b1538'],
                                'gray' => ['name' => 'Gray', 'color' => '#616061'],
                                'mood-indigo' => ['name' => 'Mood Indigo', 'color' => '#1d1c2d'],
                            ];
                        @endphp
                        @foreach ($themes as $key => $theme)
                            <label class="cursor-pointer">
                                <input type="radio" name="slackTheme" value="{{ $key }}"
                                    wire:model="slackTheme" wire:change="updateSlackTheme" class="sr-only peer" />
                                <div class="flex items-center gap-3 p-3 rounded-lg border-2 transition-all hover:border-blue-400 peer-checked:border-blue-500 peer-checked:bg-blue-50"
                                    style="border-color: #374151; background-color: rgba(255, 255, 255, 0.05);">
                                    <div class="w-6 h-6 rounded-full flex-shrink-0"
                                        style="background-color: {{ $theme['color'] }};"></div>
                                    <span class="text-sm font-semibold"
                                        style="color: #ffffff;">{{ $theme['name'] }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- 見やすい配色（視覚補助） --}}
                <div>
                    <h3 class="text-lg font-bold mb-4" style="color: #ffffff;">見やすい配色 (視覚補助)</h3>
                    <div class="space-y-3">
                        <label class="cursor-pointer block">
                            <input type="radio" name="accessibilityTheme" value="tritanopia"
                                wire:model="accessibilityTheme" wire:change="updateAccessibilityTheme"
                                class="sr-only peer" />
                            <div class="flex items-center gap-3 p-4 rounded-lg border-2 transition-all hover:border-blue-400 peer-checked:border-blue-500 peer-checked:bg-blue-50"
                                style="border-color: #374151; background-color: rgba(255, 255, 255, 0.05);">
                                <div
                                    class="w-8 h-8 rounded-full flex-shrink-0 bg-gradient-to-b from-gray-900 to-gray-700">
                                </div>
                                <span class="font-semibold" style="color: #ffffff;">Tritanopia</span>
                            </div>
                        </label>

                        <label class="cursor-pointer block">
                            <input type="radio" name="accessibilityTheme" value="protanopia-deuteranopia"
                                wire:model="accessibilityTheme" wire:change="updateAccessibilityTheme"
                                class="sr-only peer" />
                            <div class="flex items-center gap-3 p-4 rounded-lg border-2 transition-all hover:border-blue-400 peer-checked:border-blue-500 peer-checked:bg-blue-50"
                                style="border-color: #374151; background-color: rgba(255, 255, 255, 0.05);">
                                <div
                                    class="w-8 h-8 rounded-full flex-shrink-0 bg-gradient-to-b from-purple-900 to-purple-700">
                                </div>
                                <span class="font-semibold" style="color: #ffffff;">Protanopia & Deuteranopia</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        @endif
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
</script>
