@extends('layouts.app', [
    'title' => 'Contas - NOME_DO_SAAS',
    'mobileTitle' => 'Contas',
    'todayCount' => $todayCount ?? 5,
    'lateCount' => $lateCount ?? 2,
])

@section('content')
@php
$bills = $bills ?? collect([
    ['id' => 1, 'descricao' => 'Internet Fibra', 'categoria' => 'Casa', 'vencimento' => '2026-02-10', 'valor' => 129.90, 'status' => 'OPEN'],
    ['id' => 2, 'descricao' => 'Streaming', 'categoria' => 'Lazer', 'vencimento' => '2026-02-08', 'valor' => 49.90, 'status' => 'PAID'],
    ['id' => 3, 'descricao' => 'Energia', 'categoria' => 'Casa', 'vencimento' => '2026-02-01', 'valor' => 240.12, 'status' => 'LATE'],
]);
@endphp

<div x-data="billsPage()" class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-xl font-semibold">Contas</h1>
        <div class="flex items-center gap-2">
            <x-button variant="ghost" class="lg:hidden" @click="drawers.filters = true">Filtros</x-button>
            <x-button @click="modals.create = true">+ Nova conta</x-button>
        </div>
    </div>

    <x-card>
        <form class="hidden grid-cols-4 gap-3 lg:grid">
            <x-select label="Mês" name="mes"><option>Fevereiro/2026</option></x-select>
            <x-select label="Status" name="status"><option>Todos</option><option>OPEN</option><option>PAID</option><option>LATE</option></x-select>
            <x-select label="Categoria" name="categoria"><option>Todas</option><option>Casa</option><option>Lazer</option></x-select>
            <x-input label="Busca" name="busca" placeholder="Descrição..." />
        </form>

        <div class="mt-4 flex flex-wrap gap-2">
            <x-badge class="cursor-pointer" @click="quick='hoje'">Hoje</x-badge>
            <x-badge class="cursor-pointer" @click="quick='atraso'" variant="danger">Atraso</x-badge>
            <x-badge class="cursor-pointer" @click="quick='aberto'" variant="warning">Em aberto</x-badge>
            <x-badge class="cursor-pointer" @click="quick='pagas'" variant="success">Pagas</x-badge>
            <x-badge class="cursor-pointer" @click="quick='todas'">Todas</x-badge>
        </div>
    </x-card>

    <x-card class="hidden lg:block">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-muted">
                    <tr class="border-b border-white/10">
                        <th class="px-3 py-2">Descrição</th><th class="px-3 py-2">Categoria</th><th class="px-3 py-2">Vencimento</th><th class="px-3 py-2">Valor</th><th class="px-3 py-2">Status</th><th class="px-3 py-2 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bills as $bill)
                    <tr class="border-b border-white/5">
                        <td class="px-3 py-3">{{ $bill['descricao'] }}</td>
                        <td class="px-3 py-3 text-muted">{{ $bill['categoria'] }}</td>
                        <td class="px-3 py-3">{{ \Carbon\Carbon::parse($bill['vencimento'])->format('d/m/Y') }}</td>
                        <td class="px-3 py-3">R$ {{ number_format($bill['valor'], 2, ',', '.') }}</td>
                        <td class="px-3 py-3">
                            <x-badge :variant="$bill['status'] === 'PAID' ? 'success' : ($bill['status'] === 'LATE' ? 'danger' : 'warning')">{{ $bill['status'] }}</x-badge>
                        </td>
                        <td class="px-3 py-3">
                            <div class="flex justify-end gap-2">
                                @if($bill['status'] === 'OPEN' || $bill['status'] === 'LATE')
                                    <x-button size="sm" variant="secondary" @click="selected = {{ json_encode($bill) }}; modals.pay = true">Pagar</x-button>
                                @endif
                                @if($bill['status'] === 'PAID')
                                    <x-button size="sm" variant="ghost" @click="selected = {{ json_encode($bill) }}; modals.reopen = true">Reabrir</x-button>
                                @endif
                                <x-button size="sm" variant="ghost" @click="selected = {{ json_encode($bill) }}; modals.edit = true">Editar</x-button>
                            </div>
                        </td>
                    </tr>
                    @empty
                        <tr><td colspan="6" class="px-3 py-8"><x-empty-state /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    <div class="grid gap-3 lg:hidden">
        @forelse($bills as $bill)
            <x-card>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="font-medium">{{ $bill['descricao'] }}</p>
                        <p class="text-sm text-muted">{{ $bill['categoria'] }} • {{ \Carbon\Carbon::parse($bill['vencimento'])->format('d/m/Y') }}</p>
                        <p class="mt-2 text-lg font-semibold">R$ {{ number_format($bill['valor'], 2, ',', '.') }}</p>
                    </div>
                    <x-badge :variant="$bill['status'] === 'PAID' ? 'success' : ($bill['status'] === 'LATE' ? 'danger' : 'warning')">{{ $bill['status'] }}</x-badge>
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    @if($bill['status'] === 'OPEN' || $bill['status'] === 'LATE')
                        <x-button size="sm" variant="secondary" @click="selected = {{ json_encode($bill) }}; modals.pay = true">Pagar</x-button>
                    @endif
                    @if($bill['status'] === 'PAID')
                        <x-button size="sm" variant="ghost" @click="selected = {{ json_encode($bill) }}; modals.reopen = true">Reabrir</x-button>
                    @endif
                    <x-button size="sm" variant="ghost" @click="selected = {{ json_encode($bill) }}; modals.edit = true">Editar</x-button>
                </div>
            </x-card>
        @empty
            <x-empty-state actionLabel="Criar conta" actionHref="{{ url('/contas?open=create') }}" />
        @endforelse
    </div>

    <footer class="rounded-xl2 border border-white/10 bg-surface px-4 py-3 text-sm text-muted">
        Totais do mês: <span class="font-medium text-text">R$ {{ number_format(($bills->sum('valor') ?? 0), 2, ',', '.') }}</span>
        • Pagas: <span class="font-medium text-emerald-300">R$ {{ number_format($bills->where('status', 'PAID')->sum('valor'), 2, ',', '.') }}</span>
    </footer>

    <x-modal name="create" title="Nova conta" description="Cadastre uma nova conta.">
        <form class="space-y-3">
            <x-input label="Descrição" name="descricao" placeholder="Ex: Internet" />
            <div class="grid grid-cols-2 gap-3">
                <x-input label="Valor" name="valor" type="number" step="0.01" />
                <x-input label="Vencimento" name="vencimento" type="date" />
            </div>
            <x-select label="Categoria" name="categoria"><option>Casa</option><option>Lazer</option></x-select>
            <div class="flex justify-end gap-2 pt-2">
                <x-button variant="ghost" @click="modals.create = false" type="button">Cancelar</x-button>
                <x-button type="button" @click="toast('Conta criada com sucesso!'); modals.create = false">Salvar</x-button>
            </div>
        </form>
    </x-modal>

    <x-modal name="edit" title="Editar conta" description="Atualize os dados da conta.">
        <form class="space-y-3">
            <x-input label="Descrição" name="descricao" x-model="selected.descricao" />
            <div class="grid grid-cols-2 gap-3">
                <x-input label="Valor" name="valor" type="number" step="0.01" x-model="selected.valor" />
                <x-input label="Vencimento" name="vencimento" type="date" x-model="selected.vencimento" />
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <x-button variant="ghost" @click="modals.edit = false" type="button">Cancelar</x-button>
                <x-button type="button" @click="toast('Conta atualizada!'); modals.edit = false">Salvar alterações</x-button>
            </div>
        </form>
    </x-modal>

    <x-modal name="pay" title="Dar baixa" description="Confirma o pagamento desta conta?">
        <p class="text-sm text-muted">Conta: <span class="text-text" x-text="selected.descricao"></span></p>
        <div class="mt-5 flex justify-end gap-2">
            <x-button variant="ghost" @click="modals.pay = false" type="button">Cancelar</x-button>
            <x-button type="button" @click="toast('Conta marcada como paga.'); modals.pay = false">Confirmar pagamento</x-button>
        </div>
    </x-modal>

    <x-modal name="reopen" title="Reabrir conta" description="Deseja reabrir a conta paga?">
        <p class="text-sm text-muted">Conta: <span class="text-text" x-text="selected.descricao"></span></p>
        <div class="mt-5 flex justify-end gap-2">
            <x-button variant="ghost" @click="modals.reopen = false" type="button">Cancelar</x-button>
            <x-button variant="danger" type="button" @click="toast('Conta reaberta.'); modals.reopen = false">Reabrir</x-button>
        </div>
    </x-modal>

    <x-drawer name="filters" title="Filtros">
        <form class="space-y-3">
            <x-select label="Mês" name="mes"><option>Fevereiro/2026</option></x-select>
            <x-select label="Status" name="status"><option>Todos</option><option>OPEN</option><option>PAID</option><option>LATE</option></x-select>
            <x-select label="Categoria" name="categoria"><option>Todas</option><option>Casa</option><option>Lazer</option></x-select>
            <x-input label="Busca" name="busca" placeholder="Descrição..." />
            <x-button class="w-full" type="button" @click="drawers.filters = false">Aplicar filtros</x-button>
        </form>
    </x-drawer>

    <x-toast name="toastState" variant="info" />
</div>
@endsection

@push('scripts')
<script>
    function billsPage() {
        return {
            quick: 'todas',
            selected: {},
            modals: { create: false, edit: false, pay: false, reopen: false },
            drawers: { filters: false },
            toastState: { show: false, message: '' },
            toast(message) {
                this.toastState.message = message;
                this.toastState.show = true;
                setTimeout(() => this.toastState.show = false, 2200);
            },
        }
    }
</script>
@endpush
