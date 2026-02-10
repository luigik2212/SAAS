<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $now = Carbon::now('America/Sao_Paulo');
        $month = (string) $request->string('month', $now->format('Y-m'));

        if (preg_match('/^\d{4}-\d{2}$/', $month) !== 1) {
            $month = $now->format('Y-m');
        }

        $currentMonth = Carbon::createFromFormat('Y-m', $month, 'America/Sao_Paulo');
        $previousMonth = $currentMonth->copy()->subMonth();

        $currentStart = $currentMonth->copy()->startOfMonth()->toDateString();
        $currentEnd = $currentMonth->copy()->endOfMonth()->toDateString();

        $prevStart = $previousMonth->copy()->startOfMonth()->toDateString();
        $prevEnd = $previousMonth->copy()->endOfMonth()->toDateString();

        $today = $now->toDateString();

        $currentBase = Bill::query()
            ->where('user_id', auth()->id())
            ->whereBetween('due_date', [$currentStart, $currentEnd]);

        $totalOpen = (int) (clone $currentBase)
            ->where('status', Bill::STATUS_OPEN)
            ->sum('amount_cents');

        $totalPaid = (int) (clone $currentBase)
            ->where('status', Bill::STATUS_PAID)
            ->sum('amount_cents');

        $totalOverdue = (int) (clone $currentBase)
            ->where('status', Bill::STATUS_OPEN)
            ->whereDate('due_date', '<', $today)
            ->sum('amount_cents');

        $dueTodayCount = (int) (clone $currentBase)
            ->where('status', Bill::STATUS_OPEN)
            ->whereDate('due_date', $today)
            ->count();

        $totalMonthCurrent = (int) (clone $currentBase)->sum('amount_cents');

        $totalMonthPrev = (int) Bill::query()
            ->where('user_id', auth()->id())
            ->whereBetween('due_date', [$prevStart, $prevEnd])
            ->sum('amount_cents');

        $diffValue = $totalMonthCurrent - $totalMonthPrev;
        $diffPercent = $totalMonthPrev === 0
            ? null
            : round(($diffValue / $totalMonthPrev) * 100, 2);

        $groupedByDay = Bill::query()
            ->selectRaw('DAY(due_date) as day, SUM(amount_cents) as total_cents')
            ->where('user_id', auth()->id())
            ->whereBetween('due_date', [$currentStart, $currentEnd])
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total_cents', 'day')
            ->map(fn ($value) => (int) $value)
            ->all();

        $daysInMonth = $currentMonth->daysInMonth;
        $series = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $series[] = [
                'day' => $day,
                'total_cents' => $groupedByDay[$day] ?? 0,
            ];
        }

        return response()->json([
            'month' => $month,
            'cards' => [
                'total_open' => $totalOpen,
                'total_paid' => $totalPaid,
                'total_overdue' => $totalOverdue,
                'due_today_count' => $dueTodayCount,
            ],
            'comparison' => [
                'total_month_current' => $totalMonthCurrent,
                'total_month_prev' => $totalMonthPrev,
                'diff_value' => $diffValue,
                'diff_percent' => $diffPercent,
            ],
            'chart' => [
                'series' => $series,
            ],
        ]);
    }
}
