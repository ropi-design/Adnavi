<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center gap-6 p-6 md:p-10">
        <div class="w-full max-w-md">
            {{-- ロゴ --}}
            <a href="{{ route('home') }}" class="flex flex-col items-center gap-4 mb-8" wire:navigate>
                <div
                    class="flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-600 to-purple-600 rounded-2xl shadow-lg">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <span class="text-2xl font-bold text-gray-900">{{ config('app.name', 'Adnavi') }}</span>
            </a>

            {{-- コンテンツ --}}
            <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-8">
                {{ $slot }}
            </div>
        </div>
    </div>
    @livewireScripts
</body>

</html>
