@props(['heading', 'subheading'])

<div class="space-y-2">
    @if (isset($heading))
        <h2 class="text-xl font-bold text-gray-900">{{ $heading }}</h2>
    @endif
    @if (isset($subheading))
        <p class="text-gray-600">{{ $subheading }}</p>
    @endif

    <div class="mt-6">
        {{ $slot }}
    </div>
</div>
