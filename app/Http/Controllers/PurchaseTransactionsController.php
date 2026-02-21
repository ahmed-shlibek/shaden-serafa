<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequests;
use Illuminate\Http\Request;

class PurchaseTransactionsController extends Controller
{
    public function __invoke(Request $request)
    {
        $from = now()->subDays(15)->startOfDay();
        $to = now()->endOfDay();

        $totalsByDay = PurchaseRequests::query()
            ->active()
            ->withCurrency()
            ->inDateRange($from, $to)
            ->selectRaw("
                DATE_FORMAT(COALESCE(api_created_at, created_at), '%Y-%m-%d') as date,

                COUNT(amount_requested) as total_transactions,

                COUNT(cbl_flag) as with_cbl_flag,

                SUM(
                    CASE
                        WHEN cbl_flag IS NULL THEN 1
                        ELSE 0
                    END
                ) as without_cbl_flag
            ")
            ->groupByRaw('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels = [];
        $totalTransactions = [];
        $withCblFlag = [];
        $withoutCblFlag = [];

        $period = \Carbon\CarbonPeriod::create($from, '1 day', $to);

        foreach ($period as $date) {
            $dateString = $date->toDateString();

            $labels[] = $dateString;
            $totalTransactions[] = (int) ($totalsByDay[$dateString]->total_transactions ?? 0);
            $withCblFlag[] = (int) ($totalsByDay[$dateString]->with_cbl_flag ?? 0);
            $withoutCblFlag[] = (int) ($totalsByDay[$dateString]->without_cbl_flag ?? 0);
        }

        return response()->json([
            'labels' => $labels,
            'requested' => $totalTransactions,
            'approved' => $withCblFlag,
            'remaining' => $withoutCblFlag,
        ]);
    }
}
