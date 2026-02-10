<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BillExportController extends Controller
{
    public function csv(Request $request): StreamedResponse
    {
        $now = Carbon::now('America/Sao_Paulo');
        $month = (string) $request->string('month', $now->format('Y-m'));

        if (preg_match('/^\d{4}-\d{2}$/', $month) !== 1) {
            $month = $now->format('Y-m');
        }

        $reference = Carbon::createFromFormat('Y-m', $month, 'America/Sao_Paulo');
        $start = $reference->copy()->startOfMonth()->toDateString();
        $end = $reference->copy()->endOfMonth()->toDateString();

        $bills = Bill::query()
            ->where('user_id', auth()->id())
            ->whereBetween('due_date', [$start, $end])
            ->orderBy('due_date')
            ->orderBy('id')
            ->get(['id', 'category_id', 'title', 'amount_cents', 'due_date', 'status', 'paid_at', 'notes']);

        $filename = "contas-{$month}.csv";

        return response()->streamDownload(function () use ($bills): void {
            $handle = fopen('php://output', 'wb');

            fputcsv($handle, ['id', 'category_id', 'title', 'amount_cents', 'due_date', 'status', 'paid_at', 'notes']);

            foreach ($bills as $bill) {
                fputcsv($handle, [
                    $bill->id,
                    $bill->category_id,
                    $bill->title,
                    $bill->amount_cents,
                    optional($bill->due_date)->toDateString(),
                    $bill->status,
                    optional($bill->paid_at)->toDateTimeString(),
                    $bill->notes,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
