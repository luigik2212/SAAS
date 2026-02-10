@extends('layouts.app', [
    'title' => 'Contas - NOME_DO_SAAS',
    'mobileTitle' => 'Contas',
    'todayCount' => $todayCount ?? 0,
    'lateCount' => $lateCount ?? 0,
])

@section('content')
<div x-data="billsPage()" x-init="init()" class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-xl font-semibold">Contas</h1>
        <x-button @click="modals.create = true">+ Nova conta</x-button>
    </div>

    <x-card>
        <form method="GET" action="{{ route('contas.index') }}" class="grid gap-3 lg:grid-cols-4">
            <x-input label="Mês" name="month" type="month" :value="$filters['month']" />
            <x-select label="Status" name="status">
                <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>Todos</option>
                <option value="open" @selected(($filters['status'] ?? null) === 'open')>Em aberto</option>
                <option value="paid" @selected(($filters['status'] ?? null) === 'paid')>Pagas</option>
                <option value="today" @selected(($filters['status'] ?? null) === 'today')>Vence hoje</option>
                <option value="overdue" @selected(($filters['status'] ?? null) === 'overdue')>Em atraso</option>
            </x-select>
            <x-select label="Categoria" name="category_id">
                <option value="">Todas</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) ($filters['category_id'] ?? '') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </x-select>
            <x-input label="Busca" name="search" placeholder="Título ou observação..." :value="$filters['search'] ?? ''" />
            <div class="lg:col-span-4 flex justify-end gap-2">
                <x-button variant="ghost" type="button" onclick="window.location='{{ route('contas.index') }}'">Limpar</x-button>
                <x-button type="submit">Aplicar filtros</x-button>
            </div>
        </form>
    </x-card>

    <template x-if="loading">
        <x-card>
            <div class="space-y-3 animate-pulse">
                <div class="h-4 rounded bg-white/10"></div>
                <div class="h-4 rounded bg-white/10"></div>
                <div class="h-4 rounded bg-white/10"></div>
            </div>
        </x-card>
    </template>

    <div x-show="!loading" class="space-y-5">
        @if(session('error'))
            <x-empty-state title="Ocorreu um erro" :description="session('error')" />
        @elseif($isMonthEmpty)
            <x-empty-state title="Sem contas neste mês" description="Ainda não há contas cadastradas para o mês selecionado." actionLabel="Criar conta" actionHref="#" />
        @elseif(($bills->total() ?? 0) === 0 && $hasFilters)
            <x-empty-state title="Sem resultados" description="Nenhuma conta encontrada com os filtros aplicados." actionLabel="Remover filtros" :actionHref="route('contas.index', ['month' => $filters['month']])" />
        @else
            <x-card>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-muted">
                            <tr class="border-b border-white/10">
                                <th class="px-3 py-2">Título</th>
                                <th class="px-3 py-2">Categoria</th>
                                <th class="px-3 py-2">Vencimento</th>
                                <th class="px-3 py-2">Valor</th>
                                <th class="px-3 py-2">Status</th>
                                <th class="px-3 py-2 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bills as $bill)
                                <tr class="border-b border-white/5">
                                    <td class="px-3 py-3">{{ $bill->title }}</td>
                                    <td class="px-3 py-3 text-muted">{{ $bill->category?->name ?? 'Sem categoria' }}</td>
                                    <td class="px-3 py-3">{{ $bill->due_date?->format('d/m/Y') }}</td>
                                    <td class="px-3 py-3">R$ {{ number_format($bill->amount_cents / 100, 2, ',', '.') }}</td>
                                    <td class="px-3 py-3">
                                        <x-badge :variant="$bill->status === 'PAID' ? 'success' : ($bill->due_date?->isPast() ? 'danger' : 'warning')">{{ $bill->status }}</x-badge>
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="flex justify-end gap-2">
                                            @if($bill->status === 'OPEN')
                                                <form method="POST" action="{{ route('contas.pay', $bill) }}">
                                                    @csrf
                                                    <x-button size="sm" variant="secondary" type="submit">Pagar</x-button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('contas.reopen', $bill) }}">
                                                    @csrf
                                                    <x-button size="sm" variant="ghost" type="submit">Reabrir</x-button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('contas.destroy', $bill) }}">
                                                @csrf
                                                @method('DELETE')
                                                <x-button size="sm" variant="danger" type="submit">Excluir</x-button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="pt-4">{{ $bills->links() }}</div>
            </x-card>
        @endif
    </div>

    <footer class="rounded-xl2 border border-white/10 bg-surface px-4 py-3 text-sm text-muted">
        Totais do mês: <span class="font-medium text-text">R$ {{ number_format(($totals['total_month'] ?? 0) / 100, 2, ',', '.') }}</span>
        • Pagas: <span class="font-medium text-emerald-300">R$ {{ number_format(($totals['total_paid'] ?? 0) / 100, 2, ',', '.') }}</span>
        • Em aberto: <span class="font-medium text-amber-300">R$ {{ number_format(($totals['total_open'] ?? 0) / 100, 2, ',', '.') }}</span>
    </footer>

    <x-modal name="create" title="Nova conta" description="Cadastre uma nova conta.">
        <form method="POST" action="{{ route('contas.store') }}" class="space-y-3">
            @csrf
            <x-input label="Título" name="title" placeholder="Ex: Internet" />
            <div class="grid grid-cols-2 gap-3">
                <x-input label="Valor (centavos)" name="amount_cents" type="number" min="1" />
                <x-input label="Vencimento" name="due_date" type="date" />
            </div>
            <x-select label="Categoria" name="category_id">
                <option value="">Selecione</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) old('category_id') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </x-select>
            <x-input label="Observações" name="notes" placeholder="Opcional" />
            <div class="flex justify-end gap-2 pt-2">
                <x-button variant="ghost" @click="modals.create = false" type="button">Cancelar</x-button>
                <x-button type="submit">Salvar</x-button>
            </div>
        </form>
    </x-modal>

    <x-toast name="toastState" :variant="session('error') ? 'danger' : 'success'" />
</div>
@endsection

@push('scripts')
<script>
    function billsPage() {
        return {
            loading: true,
            modals: { create: false },
            toastState: { show: false, message: '' },
            init() {
                setTimeout(() => { this.loading = false; }, 450);

                const flashMessage = @json(session('success') ?? session('error'));
                if (flashMessage) {
                    this.toastState.message = flashMessage;
                    this.toastState.show = true;
                    setTimeout(() => this.toastState.show = false, 2600);
                }
            },
        }
    }
</script>
@endpush
