<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Adnavi') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Livewire Styles -->
    @livewireStyles
    @fluxStyles
</head>

<body class="font-sans antialiased">
    <flux:sidebar sticky stashable class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <flux:brand href="/" logo="https://fluxui.dev/img/demo/logo.png" name="Adnavi"
            class="px-2 dark:hidden" />
        <flux:brand href="/" logo="https://fluxui.dev/img/demo/dark-mode-logo.png" name="Adnavi"
            class="px-2 hidden dark:flex" />

        <flux:navlist variant="outline">
            <flux:navlist.item icon="home" href="/" wire:navigate>
                ダッシュボード
            </flux:navlist.item>

            <flux:navlist.group expandable heading="アカウント管理" icon="user-circle">
                <flux:navlist.item href="/accounts/google" wire:navigate>
                    Google連携
                </flux:navlist.item>
                <flux:navlist.item href="/accounts/ads" wire:navigate>
                    広告アカウント
                </flux:navlist.item>
                <flux:navlist.item href="/accounts/analytics" wire:navigate>
                    Analytics
                </flux:navlist.item>
            </flux:navlist.group>

            <flux:navlist.item icon="document-text" href="/reports" wire:navigate>
                レポート
            </flux:navlist.item>

            <flux:navlist.item icon="light-bulb" href="/insights" wire:navigate>
                インサイト
            </flux:navlist.item>

            <flux:navlist.item icon="sparkles" href="/recommendations" wire:navigate>
                改善施策
            </flux:navlist.item>

            <flux:spacer />

            <flux:navlist.item icon="cog-6-tooth" href="/settings" wire:navigate>
                設定
            </flux:navlist.item>
        </flux:navlist>

        <flux:spacer />

        <flux:dropdown position="top" align="start" class="max-lg:hidden">
            <flux:profile avatar="https://fluxui.dev/img/demo/user.png" name="{{ Auth::user()->name ?? 'ユーザー' }}" />

            <flux:menu>
                <flux:menu.item icon="arrow-right-start-on-rectangle" wire:click="logout">
                    ログアウト
                </flux:menu.item>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" alignt="start">
            <flux:profile avatar="https://fluxui.dev/img/demo/user.png" />

            <flux:menu>
                <flux:menu.item icon="arrow-right-start-on-rectangle" wire:click="logout">
                    ログアウト
                </flux:menu.item>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    <flux:main>
        <div class="max-w-7xl mx-auto">
            {{ $slot }}
        </div>
    </flux:main>

    <!-- Livewire Scripts -->
    @livewireScripts
    @fluxScripts
</body>

</html>
