<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BillExportController extends Controller
{
    public function csv(Request $request): StreamedResponse
    {
        $now = Carbon::now('America/Sao_Paulo');
        $month = (string) $request->string('month', $now->format('Y-m'));
        $status = (string) $request->string('status', 'all');

        $month = $this->normalizeMonth($month, $now);
        [$startOfMonth, $endOfMonth] = $this->monthBounds($month);

        $filename = "contas_{$month}.csv";
        $today = $now->toDateString();

        return response()->streamDownload(function () use ($startOfMonth, $endOfMonth, $status, $today): void {
            $handle = fopen('php://output', 'wb');

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'title',
                'due_date',
                'category_name',
                'amount_reais_formatado',
                'status_label',
                'paid_at',
            ]);

            $query = Bill::query()
                ->with('category:id,name')
                ->where('user_id', auth()->id())
                ->whereBetween('due_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                ->orderBy('due_date')
                ->orderBy('id');

            $this->applyStatusFilter($query, $status, $today);

            foreach ($query->cursor() as $bill) {
                fputcsv($handle, [
                    $bill->title,
                    optional($bill->due_date)->format('Y-m-d'),
                    $bill->category?->name,
                    $this->formatAmountReais((int) $bill->amount_cents),
                    $this->statusLabel($bill, $today),
                    optional($bill->paid_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function normalizeMonth(string $month, Carbon $fallbackNow): string
    {
        if (preg_match('/^\d{4}-\d{2}$/', $month) !== 1) {
            return $fallbackNow->format('Y-m');
        }

        return $month;
    }

    private function monthBounds(string $month): array
    {
        $reference = Carbon::createFromFormat('Y-m', $month, 'America/Sao_Paulo');

        return [
            $reference->copy()->startOfMonth(),
            $reference->copy()->endOfMonth(),
        ];
    }

    private function applyStatusFilter(Builder $query, string $status, string $today): void
    {
        match ($status) {
            'open' => $query->where('status', Bill::STATUS_OPEN),
            'paid' => $query->where('status', Bill::STATUS_PAID),
            'today' => $query
                ->where('status', Bill::STATUS_OPEN)
                ->whereDate('due_date', $today),
            'overdue' => $query
                ->where('status', Bill::STATUS_OPEN)
                ->whereDate('due_date', '<', $today),
            default => null,
        };
    }

    private function formatAmountReais(int $amountCents): string
    {
        return number_format($amountCents / 100, 2, ',', '.');
    }

    private function statusLabel(Bill $bill, string $today): string
    {
        if ($bill->status === Bill::STATUS_PAID) {
            return Bill::STATUS_PAID;
        }

        $dueDate = optional($bill->due_date)->toDateString();

        if ($dueDate === $today) {
            return Bill::STATUS_OPEN . ' HOJE';
        }

        if ($dueDate !== null && $dueDate < $today) {
            return Bill::STATUS_OPEN . ' ATRASO';
        }

        return Bill::STATUS_OPEN;
    }
}
