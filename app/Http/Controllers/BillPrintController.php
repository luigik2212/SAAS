<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillPrintController extends Controller
{
    public function index(Request $request): View
    {
        $now = Carbon::now('America/Sao_Paulo');
        $month = $this->normalizeMonth((string) $request->string('month', $now->format('Y-m')), $now);
        $status = (string) $request->string('status', 'all');

        [$startOfMonth, $endOfMonth] = $this->monthBounds($month);
        $today = $now->toDateString();

        $query = Bill::query()
            ->with('category:id,name')
            ->where('user_id', auth()->id())
            ->whereBetween('due_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()]);

        $totalsQuery = clone $query;

        $this->applyStatusFilter($query, $status, $today);

        $bills = $query
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();

        return view('contas.print', [
            'month' => $month,
            'status' => $status,
            'today' => $today,
            'bills' => $bills,
            'totals' => [
                'total_month' => (int) (clone $totalsQuery)->sum('amount_cents'),
                'total_open' => (int) (clone $totalsQuery)->where('status', Bill::STATUS_OPEN)->sum('amount_cents'),
                'total_paid' => (int) (clone $totalsQuery)->where('status', Bill::STATUS_PAID)->sum('amount_cents'),
                'total_overdue' => (int) (clone $totalsQuery)
                    ->where('status', Bill::STATUS_OPEN)
                    ->whereDate('due_date', '<', $today)
                    ->sum('amount_cents'),
            ],
        ]);
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
}
