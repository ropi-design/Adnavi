@extends('layouts.app')

@section('content')
    <div class="space-y-6 p-6">
        {{-- ページヘッダー --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">ダッシュボード</h1>
                <p class="mt-1 text-sm text-gray-500">
                    広告パフォーマンスの概要
                </p>
            </div>

            <div class="flex gap-3">
                <flux:button href="/reports/generate" wire:navigate icon="sparkles">
                    レポート生成
                </flux:button>
            </div>
        </div>

        {{-- ダッシュボードコンポーネント --}}
        @volt('dashboard.overview')
    </div>
@endsection
