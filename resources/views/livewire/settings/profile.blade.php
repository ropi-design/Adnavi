<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use function Livewire\Volt\{state, mount};

state([
    'name' => '',
    'email' => '',
]);

mount(function () {
    $this->name = Auth::user()->name;
    $this->email = Auth::user()->email;
});

$updateProfileInformation = function () {
    $user = Auth::user();

    $validated = $this->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
    ]);

    $user->fill($validated);

    if ($user->isDirty('email')) {
        $user->email_verified_at = null;
    }

    $user->save();

    session()->flash('message', 'プロフィールを更新しました');
    $this->dispatch('profile-updated', name: $user->name);
};

$resendVerificationNotification = function () {
    $user = Auth::user();

    if ($user->hasVerifiedEmail()) {
        $this->redirectIntended(default: route('dashboard', absolute: false));
        return;
    }

    $user->sendEmailVerificationNotification();
    Session::flash('status', 'verification-link-sent');
};

?>

<div class="p-6 lg:p-8 space-y-8 animate-fade-in">
    <div class="max-w-3xl mx-auto">
        {{-- ヘッダー --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">プロフィール設定</h1>
            <p class="text-gray-600 mt-2">名前とメールアドレスを更新できます</p>
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

        {{-- プロフィールフォーム --}}
        <div class="card p-8">
            <form wire:submit="updateProfileInformation" class="space-y-6">
                {{-- 名前 --}}
                <div>
                    <label for="name" class="block text-sm font-bold text-gray-900 mb-2">
                        名前
                    </label>
                    <input id="name" type="text" wire:model="name" required autofocus autocomplete="name"
                        class="form-input" />
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- メールアドレス --}}
                <div>
                    <label for="email" class="block text-sm font-bold text-gray-900 mb-2">
                        メールアドレス
                    </label>
                    <input id="email" type="email" wire:model="email" required autocomplete="email"
                        class="form-input" />
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    {{-- メール未確認通知 --}}
                    @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !auth()->user()->hasVerifiedEmail())
                        <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <p class="text-sm text-yellow-800 mb-2">
                                メールアドレスが未確認です。
                            </p>
                            <button type="button" wire:click="resendVerificationNotification"
                                class="text-sm text-blue-600 hover:text-blue-700 font-semibold underline">
                                確認メールを再送信
                            </button>

                            @if (session('status') === 'verification-link-sent')
                                <p class="mt-2 text-sm text-green-600 font-medium">
                                    新しい確認リンクをメールアドレスに送信しました。
                                </p>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- 保存ボタン --}}
                <div class="flex items-center gap-4 pt-4 border-t border-gray-200">
                    <button type="submit" class="btn btn-primary" data-test="update-profile-button">
                        保存
                    </button>
                </div>
            </form>
        </div>

        {{-- アカウント削除 --}}
        <div class="card p-8 mt-8 border-red-200">
            <h2 class="text-xl font-bold text-gray-900 mb-4">アカウント削除</h2>
            <p class="text-gray-600 mb-6">
                アカウントを削除すると、すべてのデータが完全に削除されます。この操作は取り消せません。
            </p>
            <button class="btn btn-danger" onclick="alert('この機能は実装中です')">
                アカウントを削除
            </button>
        </div>
    </div>
</div>
