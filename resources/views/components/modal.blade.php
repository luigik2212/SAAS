@props(['name', 'title' => '', 'description' => ''])

<div x-cloak x-show="modals.{{ $name }}" class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div @click="modals.{{ $name }} = false" class="absolute inset-0 bg-black/70"></div>
    <div class="relative w-full max-w-lg rounded-xl2 border border-white/10 bg-surface p-6 shadow-2xl">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div>
                <h3 class="text-lg font-semibold">{{ $title }}</h3>
                @if($description)
                    <p class="mt-1 text-sm text-muted">{{ $description }}</p>
                @endif
            </div>
            <button type="button" @click="modals.{{ $name }} = false" class="rounded-lg border border-white/10 px-2 py-1 text-muted">✕</button>
        </div>
        {{ $slot }}
    </div>
</div>
