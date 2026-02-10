@props(['title' => 'Nada encontrado', 'description' => 'Sem dados para exibir no momento.', 'actionLabel' => null, 'actionHref' => '#'])
<div class="rounded-xl2 border border-dashed border-white/20 bg-card/50 p-8 text-center">
    <p class="text-lg font-semibold">{{ $title }}</p>
    <p class="mt-2 text-sm text-muted">{{ $description }}</p>
    @if($actionLabel)
        <a href="{{ $actionHref }}" class="mt-4 inline-flex rounded-xl2 border border-primary/60 bg-primary/20 px-4 py-2 text-sm font-medium text-violet-200 hover:bg-primary/30">{{ $actionLabel }}</a>
    @endif
</div>
