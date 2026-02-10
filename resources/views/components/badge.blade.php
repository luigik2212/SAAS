@props(['variant' => 'default'])
@php
$styles = [
    'default' => 'border-white/10 bg-card text-muted',
    'primary' => 'border-primary/50 bg-primary/15 text-violet-200',
    'success' => 'border-emerald-500/40 bg-emerald-500/10 text-emerald-300',
    'danger' => 'border-rose-500/40 bg-rose-500/10 text-rose-300',
    'warning' => 'border-amber-500/40 bg-amber-500/10 text-amber-300',
];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full border px-3 py-1 text-xs font-medium '.($styles[$variant] ?? $styles['default'])]) }}>
    {{ $slot }}
</span>
