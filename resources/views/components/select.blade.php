@props(['label' => null, 'name' => null])
<label class="block space-y-1.5">
    @if($label)
        <span class="text-sm text-muted">{{ $label }}</span>
    @endif
    <select name="{{ $name }}" {{ $attributes->merge(['class' => 'w-full rounded-xl2 border bg-bg px-3 py-2 text-sm text-text focus:outline-none focus:ring-2 focus:ring-primary/70 '.($errors->has($name) ? 'border-rose-500/70' : 'border-white/10')]) }}>
        {{ $slot }}
    </select>
    @if($name && $errors->has($name))
        <p class="text-xs text-rose-300">{{ $errors->first($name) }}</p>
    @endif
</label>
