@props(['name' => 'toast', 'variant' => 'success'])
@php
$variants = [
    'success' => 'border-emerald-500/40 bg-emerald-500/10 text-emerald-300',
    'danger' => 'border-rose-500/40 bg-rose-500/10 text-rose-300',
    'info' => 'border-primary/40 bg-primary/10 text-violet-200',
];
@endphp

<div x-cloak x-show="{{ $name }}.show" x-transition class="fixed right-4 top-4 z-[70] rounded-xl2 border px-4 py-3 text-sm {{ $variants[$variant] ?? $variants['success'] }}">
    <p x-text="{{ $name }}.message"></p>
</div>
