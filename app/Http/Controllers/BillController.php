<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBillRequest;
use App\Http\Requests\UpdateBillRequest;
use App\Models\Bill;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BillController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $now = Carbon::now('America/Sao_Paulo');
        $month = (string) $request->string('month', $now->format('Y-m'));
        $status = (string) $request->string('status', 'all');

        [$startOfMonth, $endOfMonth] = $this->monthBounds($month, $now);

        $baseQuery = Bill::query()
            ->where('user_id', auth()->id())
            ->whereBetween('due_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()]);

        if ($request->filled('category_id')) {
            $baseQuery->where('category_id', (int) $request->input('category_id'));
        }

        if ($request->filled('search')) {
            $search = (string) $request->string('search');
            $baseQuery->where(function (Builder $query) use ($search): void {
                $query
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $filteredQuery = clone $baseQuery;
        $this->applyStatusFilter($filteredQuery, $status, $now);

        $bills = $filteredQuery
            ->with('category')
            ->orderBy('due_date')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        $totalsQuery = clone $baseQuery;

        $todayString = $now->toDateString();

        $dueTodayCount = (clone $baseQuery)
            ->whereDate('due_date', $todayString)
            ->where('status', Bill::STATUS_OPEN)
            ->count();

        $overdueCount = (clone $baseQuery)
            ->whereDate('due_date', '<', $todayString)
            ->where('status', Bill::STATUS_OPEN)
            ->count();

        $payload = [
            'filters' => [
                'month' => $month,
                'status' => $status,
                'category_id' => $request->input('category_id'),
                'search' => $request->input('search'),
            ],
            'bills' => $bills,
            'counters' => [
                'due_today_count' => $dueTodayCount,
                'overdue_count' => $overdueCount,
            ],
            'totals' => [
                'total_month' => (int) (clone $totalsQuery)->sum('amount_cents'),
                'total_open' => (int) (clone $totalsQuery)->where('status', Bill::STATUS_OPEN)->sum('amount_cents'),
                'total_paid' => (int) (clone $totalsQuery)->where('status', Bill::STATUS_PAID)->sum('amount_cents'),
                'total_overdue' => (int) (clone $totalsQuery)
                    ->where('status', Bill::STATUS_OPEN)
                    ->whereDate('due_date', '<', $todayString)
                    ->sum('amount_cents'),
            ],
        ];

        return response()->json($payload);
    }

    public function store(StoreBillRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        if (($data['status'] ?? Bill::STATUS_OPEN) === Bill::STATUS_PAID && empty($data['paid_at'])) {
            $data['paid_at'] = Carbon::now('America/Sao_Paulo');
        }

        if (($data['status'] ?? Bill::STATUS_OPEN) === Bill::STATUS_OPEN) {
            $data['paid_at'] = null;
        }

        $bill = Bill::query()->create($data);

        return response()->json($bill->fresh(['category']), 201);
    }

    public function update(UpdateBillRequest $request, Bill $bill): JsonResponse
    {
        $this->authorize('update', $bill);

        $data = $request->validated();

        if (($data['status'] ?? null) === Bill::STATUS_PAID && !array_key_exists('paid_at', $data)) {
            $data['paid_at'] = Carbon::now('America/Sao_Paulo');
        }

        if (($data['status'] ?? null) === Bill::STATUS_OPEN) {
            $data['paid_at'] = null;
        }

        $bill->update($data);

        return response()->json($bill->fresh(['category']));
    }

    public function destroy(Bill $bill): JsonResponse
    {
        $this->authorize('delete', $bill);

        $bill->delete();

        return response()->json([], 204);
    }

    public function pay(Request $request, Bill $bill): JsonResponse
    {
        $this->authorize('pay', $bill);

        $validated = $request->validate([
            'paid_at' => ['nullable', 'date'],
        ]);

        $paidAt = $validated['paid_at'] ?? Carbon::now('America/Sao_Paulo');

        $bill->update([
            'status' => Bill::STATUS_PAID,
            'paid_at' => $paidAt,
        ]);

        return response()->json($bill->fresh(['category']));
    }

    public function reopen(Bill $bill): JsonResponse
    {
        $this->authorize('reopen', $bill);

        $bill->update([
            'status' => Bill::STATUS_OPEN,
            'paid_at' => null,
        ]);

        return response()->json($bill->fresh(['category']));
    }

    private function monthBounds(string $month, Carbon $fallbackNow): array
    {
        if (preg_match('/^\d{4}-\d{2}$/', $month) !== 1) {
            $month = $fallbackNow->format('Y-m');
        }

        $reference = Carbon::createFromFormat('Y-m', $month, 'America/Sao_Paulo');

        return [
            $reference->copy()->startOfMonth(),
            $reference->copy()->endOfMonth(),
        ];
    }

    private function applyStatusFilter(Builder $query, string $status, Carbon $now): void
    {
        $today = $now->toDateString();

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
