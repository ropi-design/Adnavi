@php
    $user = auth()->user();
    $theme = 'dark';
    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'theme')) {
        $theme = $user?->theme ?? 'dark';
    }

    // テーマを解析
    $themeParts = explode('-', $theme);
    $colorMode = in_array($themeParts[0] ?? '', ['light', 'dark', 'system']) ? $themeParts[0] ?? 'dark' : 'dark';
    $themeName = $themeParts[1] ?? 'dark';

    // システムモードの場合はブラウザの設定を確認
    if ($colorMode === 'system') {
        // システム設定をJavaScriptで確認する必要があるため、デフォルトはダーク
        $colorMode = 'dark';
    }

    // テーマクラスを構築
    $themeClass = $colorMode === 'light' ? '' : 'dark';
    if ($colorMode === 'dark' && $themeName !== 'dark') {
        $themeClass .= ' theme-' . $themeName;
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ $themeClass }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
            <x-app-logo />
        </a>

        <flux:navlist variant="outline">
            <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                wire:navigate>{{ __('ダッシュボード') }}</flux:navlist.item>

            <flux:navlist.item icon="link" :href="route('accounts.google')" wire:navigate>{{ __('Google連携') }}
            </flux:navlist.item>
            <flux:navlist.item icon="folder-open" :href="route('accounts.ads')" wire:navigate>{{ __('広告アカウント') }}
            </flux:navlist.item>
            <flux:navlist.item icon="chart-bar" :href="route('accounts.analytics')" wire:navigate>
                {{ __('Analytics') }}</flux:navlist.item>

            <flux:navlist.item icon="document-text" :href="route('reports.index')" wire:navigate>{{ __('レポート') }}
            </flux:navlist.item>
            <flux:navlist.item icon="light-bulb" :href="route('insights.index')" wire:navigate>{{ __('インサイト') }}
            </flux:navlist.item>
            <flux:navlist.item icon="sparkles" :href="route('recommendations.index')" wire:navigate>
                {{ __('改善施策') }}</flux:navlist.item>
        </flux:navlist>

        <flux:spacer />
    </flux:sidebar>

    <!-- Mobile User Menu -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:button variant="ghost" class="p-0 h-auto flex items-center gap-2"
                wire:key="mobile-profile-button-{{ auth()->id() }}-{{ auth()->user()->avatar }}">
                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                    @if (auth()->user()->hasAvatar())
                        <img src="{{ auth()->user()->avatar_url }}?v={{ time() }}"
                            alt="{{ auth()->user()->name }}" class="h-full w-full object-cover rounded-lg">
                    @else
                        <span
                            class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white text-xs font-semibold">
                            {{ auth()->user()->initials() }}
                        </span>
                    @endif
                </span>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </flux:button>

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                @if (auth()->user()->hasAvatar())
                                    <img src="{{ auth()->user()->avatar_url }}?v={{ time() }}"
                                        alt="{{ auth()->user()->name }}" class="h-full w-full object-cover rounded-lg">
                                @else
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                        {{ auth()->user()->initials() }}
                                    </span>
                                @endif
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('設定') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full"
                        data-test="logout-button">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    @fluxScripts
</body>

</html>
