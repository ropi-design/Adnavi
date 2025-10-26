<?php

use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{state, mount};

state([
    'isConnected' => false,
    'connectedEmail' => null,
]);

mount(function () {
    $this->checkConnection();
});

$checkConnection = function () {
    $googleAccount = Auth::user()->googleAccounts()->first();

    $this->isConnected = $googleAccount !== null;
    $this->connectedEmail = $googleAccount?->email;
};

$connect = function () {
    $this->redirect('/auth/google');
};

$disconnect = function () {
    Auth::user()->googleAccounts()->delete();

    $this->checkConnection();

    session()->flash('message', 'Googleアカウントの連携を解除しました');
};

?>

<div class="p-6 lg:p-8">
    <div class="max-w-3xl mx-auto animate-fade-in">
        <div class="card p-8">
            {{-- ヘッダー --}}
            <div class="flex items-center gap-4 mb-8">
                <div class="p-4 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Google アカウント連携</h1>
                    <p class="text-gray-600 mt-1">広告データの分析に必要な権限を設定します</p>
                </div>
            </div>

            <div class="space-y-6">
                @if ($isConnected)
                    {{-- 連携済み --}}
                    <div
                        class="relative overflow-hidden p-8 bg-gradient-to-r from-green-50 via-emerald-50 to-green-50 border-2 border-green-300 rounded-2xl">
                        <div
                            class="absolute top-0 right-0 w-40 h-40 bg-green-200 rounded-full -mr-20 -mt-20 opacity-20">
                        </div>
                        <div
                            class="absolute bottom-0 left-0 w-32 h-32 bg-green-300 rounded-full -ml-16 -mb-16 opacity-20">
                        </div>

                        <div class="relative flex items-center gap-4">
                            <div class="p-4 bg-green-500 rounded-full shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="font-bold text-xl text-green-800 mb-2">✓ 連携済み</div>
                                <div
                                    class="text-sm text-green-700 font-mono bg-white px-3 py-2 rounded-lg inline-block">
                                    {{ $connectedEmail }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button type="button" wire:click="disconnect" class="btn btn-danger flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            連携解除
                        </button>
                    </div>
                @else
                    {{-- 未連携 --}}
                    <div class="space-y-6">
                        <div class="prose max-w-none">
                            <p class="text-gray-700 leading-relaxed">
                                <strong class="text-gray-900">Google広告とGoogleアナリティクス</strong>のデータを取得するには、
                                Googleアカウントとの連携が必要です。
                            </p>
                        </div>

                        <div class="p-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-xl">
                            <div class="flex items-center gap-3 mb-4">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h4 class="font-bold text-gray-900">必要な権限</h4>
                            </div>
                            <ul class="space-y-2 text-gray-700">
                                <li class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    Google Ads API へのアクセス
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    Google Analytics 読み取り権限
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    基本的なプロフィール情報
                                </li>
                            </ul>
                        </div>

                        <button type="button" wire:click="connect"
                            class="btn btn-primary flex items-center gap-3 px-6 py-3 text-lg w-full justify-center shadow-lg hover:shadow-xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                            Googleアカウントと連携する
                        </button>
                    </div>
                @endif

                {{-- メッセージ --}}
                @if (session('message'))
                    <div class="p-4 bg-green-100 border-l-4 border-green-500 rounded-lg shadow-sm">
                        <div class="flex items-center gap-2 text-green-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            {{ session('message') }}
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="p-4 bg-red-100 border-l-4 border-red-500 rounded-lg shadow-sm">
                        <div class="flex items-center gap-2 text-red-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            {{ session('error') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
