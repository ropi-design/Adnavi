<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>503 - メンテナンス中 | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-gradient-to-br from-yellow-50 to-orange-50">
    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="max-w-2xl w-full text-center">
            {{-- イラスト --}}
            <div class="mb-8">
                <svg class="w-64 h-64 mx-auto text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>

            {{-- エラーメッセージ --}}
            <h1 class="text-9xl font-bold text-gray-900 mb-4">503</h1>
            <h2 class="text-3xl font-bold text-gray-800 mb-4">メンテナンス中です</h2>
            <p class="text-lg text-gray-600 mb-8">
                現在、システムメンテナンスを実施しております。<br>
                ご不便をおかけして申し訳ございません。<br>
                しばらくしてから再度アクセスしてください。
            </p>

            {{-- アクション --}}
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button onclick="window.location.reload()" class="btn btn-primary inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    再読み込み
                </button>
            </div>
        </div>
    </div>
</body>

</html>
