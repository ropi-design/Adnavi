<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use function Livewire\Volt\{state};

state([
    'current_password' => '',
    'password' => '',
    'password_confirmation' => '',
]);

$updatePassword = function () {
    try {
        $validated = $this->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
        ]);
    } catch (ValidationException $e) {
        $this->reset('current_password', 'password', 'password_confirmation');
        throw $e;
    }

    Auth::user()->update([
        'password' => $validated['password'],
    ]);

    $this->reset('current_password', 'password', 'password_confirmation');

    session()->flash('message', 'パスワードを更新しました');
    $this->dispatch('password-updated');
};

?>

<div class="p-6 lg:p-8 space-y-8 animate-fade-in">
    <div class="max-w-3xl mx-auto">
        {{-- ヘッダー --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">パスワード変更</h1>
            <p class="text-gray-600 mt-2">安全性の高いパスワードを設定してください</p>
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

        {{-- パスワード変更フォーム --}}
        <div class="card p-8">
            <form wire:submit="updatePassword" class="space-y-6">
                {{-- 現在のパスワード --}}
                <div>
                    <label for="current_password" class="block text-sm font-bold text-gray-900 mb-2">
                        現在のパスワード
                    </label>
                    <input id="current_password" type="password" wire:model="current_password" required
                        autocomplete="current-password" class="form-input" />
                    @error('current_password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 新しいパスワード --}}
                <div>
                    <label for="password" class="block text-sm font-bold text-gray-900 mb-2">
                        新しいパスワード
                    </label>
                    <input id="password" type="password" wire:model="password" required autocomplete="new-password"
                        class="form-input" />
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-600">8文字以上で設定してください</p>
                </div>

                {{-- パスワード確認 --}}
                <div>
                    <label for="password_confirmation" class="block text-sm font-bold text-gray-900 mb-2">
                        新しいパスワード（確認）
                    </label>
                    <input id="password_confirmation" type="password" wire:model="password_confirmation" required
                        autocomplete="new-password" class="form-input" />
                </div>

                {{-- 保存ボタン --}}
                <div class="flex items-center gap-4 pt-4 border-t border-gray-200">
                    <button type="submit" class="btn btn-primary" data-test="update-password-button">
                        パスワードを更新
                    </button>
                </div>
            </form>
        </div>

        {{-- セキュリティヒント --}}
        <div class="card p-6 bg-blue-50 border-blue-200">
            <h3 class="text-lg font-bold text-gray-900 mb-3">パスワードのヒント</h3>
            <ul class="space-y-2 text-sm text-gray-700">
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    8文字以上の長さ
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    大文字と小文字を組み合わせる
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    数字と記号を含める
                </li>
            </ul>
        </div>
    </div>
</div>
