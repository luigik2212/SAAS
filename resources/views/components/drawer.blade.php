@props(['name', 'title' => ''])

<div x-cloak x-show="drawers.{{ $name }}" class="fixed inset-0 z-50 lg:hidden">
    <div @click="drawers.{{ $name }} = false" class="absolute inset-0 bg-black/70"></div>
    <div class="absolute right-0 top-0 h-full w-80 max-w-[90vw] border-l border-white/10 bg-surface p-5">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="font-semibold">{{ $title }}</h3>
            <button type="button" @click="drawers.{{ $name }} = false" class="rounded-lg border border-white/10 px-2 py-1 text-muted">Fechar</button>
        </div>
        {{ $slot }}
    </div>
</div>
