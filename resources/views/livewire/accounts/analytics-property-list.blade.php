<?php

use App\Models\AnalyticsProperty;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{state, mount};

state(['analyticsProperties' => []]);

mount(function () {
    // 一時的にサンプルデータで表示
    $sampleData = [
        (object) [
            'id' => 1,
            'property_name' => 'サンプルAnalyticsプロパティ 1',
            'property_id' => 'GA4-123456789',
            'timezone' => 'Asia/Tokyo',
            'last_synced_at' => now()->subHours(3),
            'googleAccount' => (object) ['email' => 'sample@example.com'],
        ],
        (object) [
            'id' => 2,
            'property_name' => 'サンプルAnalyticsプロパティ 2',
            'property_id' => 'GA4-234567890',
            'timezone' => 'Asia/Tokyo',
            'last_synced_at' => now()->subDays(1),
            'googleAccount' => (object) ['email' => 'sample2@example.com'],
        ],
    ];

    $this->analyticsProperties = collect($sampleData)->map(function ($item) {
        // last_synced_atをCarbonインスタンスに変換
        if ($item->last_synced_at) {
            $item->last_synced_at = \Carbon\Carbon::parse($item->last_synced_at);
        }
        return $item;
    });

    // 本番用のコード（コメントアウト）
    // $this->analyticsProperties = AnalyticsProperty::where('user_id', Auth::id())->where('is_active', true)->with('googleAccount')->latest()->get();
});

?>

<div class="space-y-6 p-6 lg:p-8">
    {{-- 戻るボタン --}}
    <div class="mb-4">
        <a href="/accounts/analytics"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-colors border-2"
            style="background-color: #ffffff; color: #000000; border-color: #e5e7eb;">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            詳細画面に戻る
        </a>
    </div>

    {{-- ヘッダー --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold" style="color: #ffffff;">Analyticsプロパティ一覧</h1>
            <p class="mt-1" style="color: #ffffff;">連携中のAnalyticsプロパティを管理</p>
        </div>
        <a href="/accounts/google"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-colors border-2"
            style="background-color: #ffffff; color: #000000; border-color: #e5e7eb;">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
            </svg>
            Google連携
        </a>
    </div>

    {{-- プロパティリスト --}}
    @if ($analyticsProperties->count() > 0)
        <div class="space-y-4">
            @foreach ($analyticsProperties as $property)
                <div class="card p-6 hover:shadow-xl transition-all duration-300">
                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-purple-100 rounded-lg flex-shrink-0">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-xl mb-1" style="color: #ffffff;">{{ $property->property_name }}
                            </h3>
                            <p class="text-sm text-gray-500 font-mono mb-3">ID: {{ $property->property_id }}</p>

                            <div class="flex flex-wrap items-center gap-4 text-sm bg-gray-50 rounded-lg p-3">
                                <div>
                                    <span class="text-gray-600">タイムゾーン:</span>
                                    <span class="font-bold text-gray-900 ml-2">{{ $property->timezone }}</span>
                                </div>
                                @if ($property->last_synced_at)
                                    <div>
                                        <span class="text-gray-600">最終同期:</span>
                                        <span
                                            class="font-semibold text-blue-600 ml-2">{{ $property->last_synced_at->diffForHumans() }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <a href="/accounts/analytics/{{ $property->id }}"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-colors border-2"
                            style="background-color: #ffffff; color: #000000; border-color: #e5e7eb;">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            詳細
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card">
            <div class="text-center py-16 text-gray-500">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-6">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">Analyticsプロパティが登録されていません</h3>
                <p class="text-gray-500 mb-6">Googleアカウントと連携して始めましょう</p>
                <a href="/accounts/google" class="btn btn-primary inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                    Googleアカウントと連携
                </a>
            </div>
        </div>
    @endif
</div>
