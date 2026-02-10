@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
])

@php
$variants = [
    'primary' => 'bg-primary text-white border-primary hover:bg-violet-500',
    'secondary' => 'bg-card text-text border-white/10 hover:border-primary/70',
    'ghost' => 'bg-transparent text-muted border-white/10 hover:bg-card hover:text-text',
    'danger' => 'bg-rose-600/90 text-white border-rose-500 hover:bg-rose-500',
];
$sizes = [
    'sm' => 'px-3 py-1.5 text-sm',
    'md' => 'px-4 py-2 text-sm',
    'lg' => 'px-5 py-3 text-base',
];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => 'inline-flex items-center justify-center gap-2 rounded-xl2 border font-medium transition focus:outline-none focus:ring-2 focus:ring-primary/70 '.($variants[$variant] ?? $variants['primary']).' '.($sizes[$size] ?? $sizes['md'])]) }}>
    {{ $slot }}
</button>
