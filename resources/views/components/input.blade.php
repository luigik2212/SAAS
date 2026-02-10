@props(['label' => null, 'name' => null, 'type' => 'text'])
<label class="block space-y-1.5">
    @if($label)
        <span class="text-sm text-muted">{{ $label }}</span>
    @endif
    <input type="{{ $type }}" name="{{ $name }}" {{ $attributes->merge(['class' => 'w-full rounded-xl2 border border-white/10 bg-bg px-3 py-2 text-sm text-text placeholder:text-muted/80 focus:outline-none focus:ring-2 focus:ring-primary/70']) }}>
</label>
