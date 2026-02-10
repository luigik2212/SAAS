<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BillPrintController extends Controller
{
    public function index(Request $request): JsonResponse
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
            ->with('category')
            ->where('user_id', auth()->id())
            ->whereBetween('due_date', [$start, $end])
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();

        return response()->json([
            'month' => $month,
            'bills' => $bills,
            'total' => (int) $bills->sum('amount_cents'),
        ]);
    }
}
