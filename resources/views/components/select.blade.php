@props(['label' => null, 'name' => null])
<label class="block space-y-1.5">
    @if($label)
        <span class="text-sm text-muted">{{ $label }}</span>
    @endif
    <select name="{{ $name }}" {{ $attributes->merge(['class' => 'w-full rounded-xl2 border border-white/10 bg-bg px-3 py-2 text-sm text-text focus:outline-none focus:ring-2 focus:ring-primary/70']) }}>
        {{ $slot }}
    </select>
</label>
