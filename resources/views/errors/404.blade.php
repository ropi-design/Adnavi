<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - ページが見つかりません | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-gradient-to-br from-gray-50 to-gray-100">
    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="max-w-2xl w-full text-center">
            {{-- イラスト --}}
            <div class="mb-8">
                <svg class="w-64 h-64 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            {{-- エラーメッセージ --}}
            <h1 class="text-9xl font-bold text-gray-900 mb-4">404</h1>
            <h2 class="text-3xl font-bold text-gray-800 mb-4">ページが見つかりません</h2>
            <p class="text-lg text-gray-600 mb-8">
                お探しのページは存在しないか、移動した可能性があります。
            </p>

            {{-- アクション --}}
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/" class="btn btn-primary inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    ホームに戻る
                </a>
                <button onclick="window.history.back()" class="btn btn-secondary inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    前のページに戻る
                </button>
            </div>
        </div>
    </div>
</body>

</html>
