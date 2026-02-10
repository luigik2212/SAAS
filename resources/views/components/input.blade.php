@props(['label' => null, 'name' => null, 'type' => 'text'])
<label class="block space-y-1.5">
    @if($label)
        <span class="text-sm text-muted">{{ $label }}</span>
    @endif
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        value="{{ old($name, $attributes->get('value')) }}"
        {{ $attributes->except('value')->merge(['class' => 'w-full rounded-xl2 border bg-bg px-3 py-2 text-sm text-text placeholder:text-muted/80 focus:outline-none focus:ring-2 focus:ring-primary/70 '.($errors->has($name) ? 'border-rose-500/70' : 'border-white/10')]) }}
    >
    @if($name && $errors->has($name))
        <p class="text-xs text-rose-300">{{ $errors->first($name) }}</p>
    @endif
</label>
