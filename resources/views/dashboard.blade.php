@extends('layouts.app', [
    'title' => 'Dashboard - NOME_DO_SAAS',
    'mobileTitle' => 'Dashboard',
    'todayCount' => $todayCount ?? 5,
    'lateCount' => $lateCount ?? 2,
])

@section('content')
<div class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-card title="Em aberto">
            <p class="text-2xl font-semibold">R$ {{ number_format($stats['open'] ?? 4520.45, 2, ',', '.') }}</p>
        </x-card>
        <x-card title="Pago">
            <p class="text-2xl font-semibold text-emerald-300">R$ {{ number_format($stats['paid'] ?? 3210.00, 2, ',', '.') }}</p>
        </x-card>
        <x-card title="Em atraso">
            <p class="text-2xl font-semibold text-rose-300">R$ {{ number_format($stats['late'] ?? 980.10, 2, ',', '.') }}</p>
        </x-card>
        <x-card title="Vence hoje">
            <p class="text-2xl font-semibold text-amber-300">R$ {{ number_format($stats['due_today'] ?? 430.00, 2, ',', '.') }}</p>
        </x-card>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1.25fr_1fr]">
        <x-card title="Comparativo mensal" subtitle="Mês atual vs anterior">
            @php
                $currentMonth = $comparison['current'] ?? 4520.50;
                $previousMonth = $comparison['previous'] ?? 3960.20;
                $change = $previousMonth > 0 ? (($currentMonth - $previousMonth) / $previousMonth) * 100 : 0;
            @endphp
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl2 border border-white/10 bg-bg p-4">
                    <p class="text-sm text-muted">Mês atual</p>
                    <p class="mt-2 text-2xl font-semibold">R$ {{ number_format($currentMonth, 2, ',', '.') }}</p>
                </div>
                <div class="rounded-xl2 border border-white/10 bg-bg p-4">
                    <p class="text-sm text-muted">Mês anterior</p>
                    <p class="mt-2 text-2xl font-semibold">R$ {{ number_format($previousMonth, 2, ',', '.') }}</p>
                </div>
            </div>
            <x-badge class="mt-4" :variant="$change >= 0 ? 'danger' : 'success'">
                {{ $change >= 0 ? '+' : '' }}{{ number_format($change, 1, ',', '.') }}%
            </x-badge>
        </x-card>

        <x-card title="Atalhos">
            <div class="space-y-3">
                <x-button class="w-full justify-between" variant="secondary" onclick="window.location='{{ url('/contas?quick=hoje') }}'">
                    Ver Vence hoje <span>→</span>
                </x-button>
                <x-button class="w-full justify-between" variant="secondary" onclick="window.location='{{ url('/contas?quick=atraso') }}'">
                    Ver Em atraso <span>→</span>
                </x-button>
            </div>
        </x-card>
    </div>

    <x-card title="Fluxo da semana" subtitle="Resumo visual">
        <div class="h-72">
            <canvas id="overviewChart"></canvas>
        </div>
    </x-card>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('overviewChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
                datasets: [{
                    label: 'Contas (R$)',
                    data: [420, 510, 380, 690, 570, 300, 480],
                    borderColor: '#7C3AED',
                    backgroundColor: 'rgba(124,58,237,.2)',
                    fill: true,
                    tension: .35,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: '#EDEDF7' } }
                },
                scales: {
                    x: { ticks: { color: '#A9A9C2' }, grid: { color: 'rgba(255,255,255,.06)' } },
                    y: { ticks: { color: '#A9A9C2' }, grid: { color: 'rgba(255,255,255,.06)' } },
                }
            }
        });
    }
</script>
@endpush
