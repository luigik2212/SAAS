@props(['title' => null, 'subtitle' => null])
<div {{ $attributes->merge(['class' => 'rounded-xl2 border border-white/10 bg-card p-5']) }}>
    @if($title)
        <div class="mb-4">
            <h3 class="text-base font-semibold text-text">{{ $title }}</h3>
            @if($subtitle)
                <p class="mt-1 text-sm text-muted">{{ $subtitle }}</p>
            @endif
        </div>
    @endif
    {{ $slot }}
</div>
