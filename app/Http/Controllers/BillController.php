<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBillRequest;
use App\Http\Requests\UpdateBillRequest;
use App\Models\Bill;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillController extends Controller
{
    public function index(Request $request): JsonResponse|View
    {
        $this->authorize('viewAny', Bill::class);

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

        if ($request->wantsJson()) {
            return response()->json($payload);
        }

        $this->authorize('viewAny', Category::class);

        $categories = Category::query()
            ->where('user_id', auth()->id())
            ->orderBy('name')
            ->get();

        $totalMonthBills = Bill::query()
            ->where('user_id', auth()->id())
            ->whereBetween('due_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->count();

        return view('bills.index', [
            'bills' => $bills,
            'categories' => $categories,
            'filters' => $payload['filters'],
            'totals' => $payload['totals'],
            'todayCount' => $dueTodayCount,
            'lateCount' => $overdueCount,
            'isMonthEmpty' => $totalMonthBills === 0,
            'hasFilters' => $request->filled('search') || $request->filled('category_id') || $status !== 'all',
        ]);
    }

    public function store(StoreBillRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('create', Bill::class);

        $data = $request->validated();
        $data['user_id'] = auth()->id();

        if (($data['status'] ?? Bill::STATUS_OPEN) === Bill::STATUS_PAID && empty($data['paid_at'])) {
            $data['paid_at'] = Carbon::now('America/Sao_Paulo');
        }

        if (($data['status'] ?? Bill::STATUS_OPEN) === Bill::STATUS_OPEN) {
            $data['paid_at'] = null;
        }

        $bill = Bill::query()->create($data);

        if ($request->wantsJson()) {
            return response()->json($bill->fresh(['category']), 201);
        }

        return to_route('contas.index')->with('success', 'Conta criada com sucesso.');
    }

    public function update(UpdateBillRequest $request, Bill $bill): JsonResponse|RedirectResponse
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

        if ($request->wantsJson()) {
            return response()->json($bill->fresh(['category']));
        }

        return to_route('contas.index')->with('success', 'Conta editada com sucesso.');
    }

    public function destroy(Request $request, Bill $bill): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $bill);

        $bill->delete();

        if ($request->wantsJson()) {
            return response()->json([], 204);
        }

        return to_route('contas.index')->with('success', 'Conta excluída com sucesso.');
    }

    public function pay(Request $request, Bill $bill): JsonResponse|RedirectResponse
    {
        $this->authorize('pay', $bill);

        $validated = $request->validate([
            'paid_at' => ['nullable', 'date'],
        ], [
            'paid_at.date' => 'Informe uma data de pagamento válida.',
        ]);

        $paidAt = $validated['paid_at'] ?? Carbon::now('America/Sao_Paulo');

        $bill->update([
            'status' => Bill::STATUS_PAID,
            'paid_at' => $paidAt,
        ]);

        if ($request->wantsJson()) {
            return response()->json($bill->fresh(['category']));
        }

        return to_route('contas.index')->with('success', 'Conta marcada como paga.');
    }

    public function reopen(Request $request, Bill $bill): JsonResponse|RedirectResponse
    {
        $this->authorize('reopen', $bill);

        $bill->update([
            'status' => Bill::STATUS_OPEN,
            'paid_at' => null,
        ]);

        if ($request->wantsJson()) {
            return response()->json($bill->fresh(['category']));
        }

        return to_route('contas.index')->with('success', 'Conta reaberta com sucesso.');
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
