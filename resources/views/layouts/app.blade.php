<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Adnavi') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Livewire Styles -->
    @livewireStyles
</head>

<body class="font-sans antialiased bg-gradient-to-br from-gray-50 to-gray-100">
    <!-- モバイル用ヘッダー -->
    <header class="lg:hidden bg-white border-b border-gray-200 shadow-sm sticky top-0 z-50">
        <div class="flex items-center justify-between p-4">
            <button id="mobile-menu-toggle" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
                <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <h1 class="text-xl font-bold text-blue-600">Adnavi</h1>
            <div class="w-10"></div> <!-- スペーサー -->
        </div>
    </header>

    <!-- メインコンテナ -->
    <div class="flex h-screen bg-gradient-to-br from-gray-50 to-gray-100">
        <!-- サイドバー -->
        <aside id="sidebar"
            class="fixed lg:static inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out shadow-lg lg:shadow-none">
            <div class="flex flex-col h-full">
                <!-- ロゴ -->
                <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-blue-600 to-blue-700">
                    <h1 class="text-2xl font-bold text-white">Adnavi</h1>
                    <p class="text-sm text-blue-100 mt-1">広告効果分析プラットフォーム</p>
                </div>

                <!-- ナビゲーション -->
                <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
                    <!-- ダッシュボード -->
                    <a href="/" class="nav-item group">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span>ダッシュボード</span>
                    </a>

                    <div class="pt-4">
                        <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                            アカウント管理
                        </p>
                        <a href="/accounts/google" class="nav-item group">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                            <span>Google連携</span>
                        </a>
                        <a href="/accounts/ads" class="nav-item group">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span>広告アカウント</span>
                        </a>
                        <a href="/accounts/analytics" class="nav-item group">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <span>Analytics</span>
                        </a>
                    </div>

                    <div class="pt-4">
                        <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                            分析・レポート
                        </p>
                        <a href="/reports" class="nav-item group">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>レポート</span>
                        </a>
                        <a href="/insights" class="nav-item group">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M6.343 6.343l-.707.707m12.728 0l-.707.707M6.343 17.657l-.707.707M17.657 6.343l-.707-.707m-12.728 0l-.707.707m12.728 12.728l-.707.707M17.657 17.657l-.707-.707M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>インサイト</span>
                        </a>
                        <a href="/recommendations" class="nav-item group">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                            <span>改善施策</span>
                        </a>
                    </div>

                    <div class="pt-4 mt-auto">
                        <a href="/settings" class="nav-item group">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>設定</span>
                        </a>
                    </div>
                </nav>
            </div>
        </aside>

        <!-- モバイルオーバーレイ -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden"></div>

        <!-- メインコンテンツ -->
        <main class="flex-1 overflow-auto">
            @yield('content')
        </main>
    </div>

    <!-- Livewire Scripts -->
    @livewireScripts

    <!-- JavaScript for mobile menu -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleButton = document.getElementById('mobile-menu-toggle');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');

            function openSidebar() {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            function closeSidebar() {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
                document.body.style.overflow = '';
            }

            toggleButton?.addEventListener('click', () => {
                if (sidebar.classList.contains('-translate-x-full')) {
                    openSidebar();
                } else {
                    closeSidebar();
                }
            });

            overlay?.addEventListener('click', closeSidebar);

            // サイドバー内のリンククリックで閉じる
            document.querySelectorAll('#sidebar a').forEach(link => {
                link.addEventListener('click', () => {
                    if (window.innerWidth < 1024) {
                        closeSidebar();
                    }
                });
            });

            // リサイズ時に自動で閉じる
            window.addEventListener('resize', () => {
                if (window.innerWidth >= 1024) {
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.add('hidden');
                } else if (!sidebar.classList.contains('-translate-x-full')) {
                    closeSidebar();
                }
            });
        });
    </script>

    <!-- Custom CSS -->
    <style>
        .nav-item {
            @apply flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-700 transition-all duration-200 font-medium;
        }

        .nav-item:hover {
            @apply bg-blue-50 text-blue-600;
        }

        .nav-item svg {
            @apply text-gray-500;
        }

        .nav-item:hover svg {
            @apply text-blue-600;
        }

        /* アクティブなメニューアイテム */
        .nav-item.active {
            @apply bg-blue-100 text-blue-700 font-semibold;
        }

        .nav-item.active svg {
            @apply text-blue-700;
        }

        /* カードスタイル */
        .card {
            @apply bg-white rounded-xl shadow-sm border border-gray-200 transition-all duration-200;
        }

        .card:hover {
            @apply shadow-md border-gray-300;
        }

        /* ボタンスタイル */
        .btn {
            @apply inline-flex items-center justify-center px-4 py-2 rounded-lg font-medium transition-all duration-200;
        }

        .btn-primary {
            @apply bg-blue-600 text-white hover:bg-blue-700 active:scale-95;
        }

        .btn-secondary {
            @apply bg-gray-100 text-gray-700 hover:bg-gray-200 active:scale-95;
        }

        .btn-success {
            @apply bg-green-600 text-white hover:bg-green-700 active:scale-95;
        }

        .btn-danger {
            @apply bg-red-600 text-white hover:bg-red-700 active:scale-95;
        }

        /* フォームスタイル */
        .form-input {
            @apply w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors;
            background-color: white !important;
            color: #111827 !important;
        }

        .form-input option {
            background-color: white !important;
            color: #111827 !important;
        }

        select.form-input {
            background-color: white !important;
            color: #111827 !important;
        }

        select.form-input option {
            background-color: white !important;
            color: #111827 !important;
        }

        input[type="date"].form-input {
            background-color: white !important;
            color: #111827 !important;
        }

        input[type="date"].form-input::-webkit-calendar-picker-indicator {
            filter: invert(0) !important;
        }

        /* アニメーション */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.3s ease-out;
        }

        /* スクロールバースタイル */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }
    </style>
</body>

</html>
