<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contas {{ $month }}</title>
    <link rel="stylesheet" href="{{ asset('css/print-contas.css') }}">
</head>
<body>
<main class="print-page">
    <header class="print-header">
        <h1>Contas do mês {{ $month }}</h1>
        <p>Filtro de status: {{ strtoupper($status) }}</p>
    </header>

    <section class="summary-grid">
        <article class="summary-card">
            <h2>Total do mês</h2>
            <p>R$ {{ number_format($totals['total_month'] / 100, 2, ',', '.') }}</p>
        </article>
        <article class="summary-card">
            <h2>Total em aberto</h2>
            <p>R$ {{ number_format($totals['total_open'] / 100, 2, ',', '.') }}</p>
        </article>
        <article class="summary-card">
            <h2>Total pago</h2>
            <p>R$ {{ number_format($totals['total_paid'] / 100, 2, ',', '.') }}</p>
        </article>
        <article class="summary-card">
            <h2>Total em atraso</h2>
            <p>R$ {{ number_format($totals['total_overdue'] / 100, 2, ',', '.') }}</p>
        </article>
    </section>

    <section class="table-section">
        <table>
            <thead>
            <tr>
                <th>Título</th>
                <th>Categoria</th>
                <th>Vencimento</th>
                <th>Status</th>
                <th>Pagamento</th>
                <th class="amount">Valor</th>
            </tr>
            </thead>
            <tbody>
            @forelse($bills as $bill)
                <tr>
                    <td>{{ $bill->title }}</td>
                    <td>{{ $bill->category?->name ?? '-' }}</td>
                    <td>{{ optional($bill->due_date)->format('d/m/Y') }}</td>
                    <td>
                        @if($bill->status === \App\Models\Bill::STATUS_PAID)
                            {{ \App\Models\Bill::STATUS_PAID }}
                        @elseif(optional($bill->due_date)->toDateString() === $today)
                            {{ \App\Models\Bill::STATUS_OPEN }} HOJE
                        @elseif(optional($bill->due_date)->toDateString() < $today)
                            {{ \App\Models\Bill::STATUS_OPEN }} ATRASO
                        @else
                            {{ \App\Models\Bill::STATUS_OPEN }}
                        @endif
                    </td>
                    <td>{{ optional($bill->paid_at)->format('d/m/Y H:i') ?? '-' }}</td>
                    <td class="amount">R$ {{ number_format($bill->amount_cents / 100, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="empty">Nenhuma conta encontrada para os filtros informados.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </section>
</main>
</body>
</html>
