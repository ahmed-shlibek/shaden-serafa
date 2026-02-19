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
            ->where('is_deleted', 0)
            ->whereIn('currency_code', ['USD', 'LYD'])
            ->where(function ($query) use ($from, $to) {
                $query->whereBetween('created_at', [$from, $to])
                    ->orWhereBetween('api_created_at', [$from, $to]);
            })
            ->selectRaw("
                DATE_FORMAT(COALESCE(api_created_at, created_at), '%Y-%m-%d') as date,

                COUNT(amount_requested) as total,

                COUNT(cbl_flag) as cbl_flag,

                SUM(
                    CASE
                        WHEN cbl_flag IS NULL THEN 1
                        ELSE 0
                    END
                ) as cbl_flag_null
            ")
            ->groupByRaw('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels = [];
        $requested = [];
        $approved = [];
        $remaining = [];

        $period = \Carbon\CarbonPeriod::create($from, '1 day', $to);

        foreach ($period as $date) {
            $dateString = $date->toDateString();

            $labels[] = $dateString;
            $requested[] = (float) ($totalsByDay[$dateString]->total ?? 0);
            $approved[] = (float) ($totalsByDay[$dateString]->cbl_flag ?? 0);
            $remaining[] = (float) ($totalsByDay[$dateString]->cbl_flag_null ?? 0);
        }

        return response()->json([
            'labels' => $labels,
            'requested' => $requested,
            'approved' => $approved,
            'remaining' => $remaining,
        ]);
    }
}
