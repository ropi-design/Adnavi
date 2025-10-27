<?php

use function Livewire\Volt\{state};

state(['theme' => 'light']);

?>

<div class="p-6 lg:p-8 space-y-8 animate-fade-in">
    <div class="max-w-3xl mx-auto">
        {{-- ヘッダー --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">外観設定</h1>
            <p class="text-gray-600 mt-2">アプリケーションの表示をカスタマイズできます</p>
        </div>

        {{-- テーマ設定 --}}
        <div class="card p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6">テーマ</h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                {{-- ライト --}}
                <label class="cursor-pointer">
                    <input type="radio" name="theme" value="light" wire:model="theme" class="sr-only peer" />
                    <div
                        class="p-6 border-2 border-gray-300 rounded-xl text-center transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-gray-400">
                        <div class="flex justify-center mb-3">
                            <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <span class="font-semibold text-gray-900">ライト</span>
                    </div>
                </label>

                {{-- ダーク --}}
                <label class="cursor-pointer">
                    <input type="radio" name="theme" value="dark" wire:model="theme" class="sr-only peer" />
                    <div
                        class="p-6 border-2 border-gray-300 rounded-xl text-center transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-gray-400">
                        <div class="flex justify-center mb-3">
                            <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                        </div>
                        <span class="font-semibold text-gray-900">ダーク</span>
                    </div>
                </label>

                {{-- システム --}}
                <label class="cursor-pointer">
                    <input type="radio" name="theme" value="system" wire:model="theme" class="sr-only peer" />
                    <div
                        class="p-6 border-2 border-gray-300 rounded-xl text-center transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-gray-400">
                        <div class="flex justify-center mb-3">
                            <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <span class="font-semibold text-gray-900">システム</span>
                    </div>
                </label>
            </div>

            <p class="mt-4 text-sm text-gray-600">
                ※ 現在はライトテーマのみ対応しています。ダークモード機能は今後実装予定です。
            </p>
        </div>

        {{-- 言語設定 --}}
        <div class="card p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6">言語</h2>

            <div class="space-y-3">
                <label class="flex items-center p-4 border-2 border-blue-500 bg-blue-50 rounded-xl cursor-pointer">
                    <input type="radio" name="language" value="ja" checked class="w-4 h-4 text-blue-600" />
                    <span class="ml-3 font-semibold text-gray-900">日本語</span>
                </label>

                <label class="flex items-center p-4 border-2 border-gray-300 rounded-xl cursor-not-allowed opacity-50">
                    <input type="radio" name="language" value="en" disabled class="w-4 h-4 text-blue-600" />
                    <span class="ml-3 font-semibold text-gray-900">English (準備中)</span>
                </label>
            </div>
        </div>

        {{-- タイムゾーン --}}
        <div class="card p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6">タイムゾーン</h2>

            <select class="form-input" disabled>
                <option>Asia/Tokyo (GMT+9)</option>
            </select>

            <p class="mt-2 text-sm text-gray-600">
                現在の時刻: {{ now()->format('Y年m月d日 H:i:s') }}
            </p>
        </div>
    </div>
</div>
