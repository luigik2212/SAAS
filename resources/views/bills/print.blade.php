<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impressão de Contas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body class="bg-white text-slate-900">
    @php
        $bills = $bills ?? collect([
            ['descricao' => 'Internet Fibra', 'categoria' => 'Casa', 'vencimento' => '2026-02-10', 'valor' => 129.90, 'status' => 'OPEN'],
            ['descricao' => 'Streaming', 'categoria' => 'Lazer', 'vencimento' => '2026-02-08', 'valor' => 49.90, 'status' => 'PAID'],
            ['descricao' => 'Energia', 'categoria' => 'Casa', 'vencimento' => '2026-02-01', 'valor' => 240.12, 'status' => 'LATE'],
        ]);
    @endphp

    <main class="mx-auto max-w-4xl px-4 py-8">
        <header class="mb-6 flex items-start justify-between border-b border-slate-200 pb-4">
            <div>
                <h1 class="text-2xl font-bold">NOME_DO_SAAS</h1>
                <p class="text-sm text-slate-500">Relatório de contas • {{ now()->format('d/m/Y H:i') }}</p>
            </div>
            <button onclick="window.print()" class="no-print rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Imprimir</button>
        </header>

        <table class="min-w-full border border-slate-200 text-sm">
            <thead class="bg-slate-50 text-left">
                <tr>
                    <th class="border-b border-slate-200 px-3 py-2">Descrição</th>
                    <th class="border-b border-slate-200 px-3 py-2">Categoria</th>
                    <th class="border-b border-slate-200 px-3 py-2">Vencimento</th>
                    <th class="border-b border-slate-200 px-3 py-2">Status</th>
                    <th class="border-b border-slate-200 px-3 py-2 text-right">Valor</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bills as $bill)
                    <tr>
                        <td class="border-b border-slate-200 px-3 py-2">{{ $bill['descricao'] }}</td>
                        <td class="border-b border-slate-200 px-3 py-2">{{ $bill['categoria'] }}</td>
                        <td class="border-b border-slate-200 px-3 py-2">{{ \Carbon\Carbon::parse($bill['vencimento'])->format('d/m/Y') }}</td>
                        <td class="border-b border-slate-200 px-3 py-2">{{ $bill['status'] }}</td>
                        <td class="border-b border-slate-200 px-3 py-2 text-right">R$ {{ number_format($bill['valor'], 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="font-semibold">
                    <td colspan="4" class="px-3 py-2 text-right">Total</td>
                    <td class="px-3 py-2 text-right">R$ {{ number_format($bills->sum('valor'), 2, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </main>
</body>
</html>
