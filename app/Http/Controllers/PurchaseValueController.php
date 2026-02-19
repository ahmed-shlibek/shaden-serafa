<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequests;
use Illuminate\Http\Request;

class PurchaseValueController extends Controller
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

                SUM(
                    CASE
                        WHEN currency_code = 'LYD' THEN amount_requested / COALESCE(bank_transfer_price, 1)
                        ELSE amount_requested
                    END
                ) as total_amount,

                COALESCE(SUM(
                    CASE
                        WHEN currency_code = 'LYD'
                                THEN COALESCE(deduct_lyd_amount, 0) / COALESCE(bank_transfer_price, 1)
                        ELSE COALESCE(deduct_lyd_amount, 0)
                    END
                ), 0) as approved_total,

                COALESCE(SUM(
                    CASE
                        WHEN currency_code = 'LYD' THEN amount_requested / COALESCE(bank_transfer_price, 1) - COALESCE(deduct_lyd_amount, 0) / COALESCE(bank_transfer_price, 1)
                        ELSE amount_requested - COALESCE(deduct_lyd_amount, 0)
                    END
                ), 0) as remaining_value
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
            $requested[] = (float) ($totalsByDay[$dateString]->total_amount ?? 0);
            $approved[] = (float) ($totalsByDay[$dateString]->approved_total ?? 0);
            $remaining[] = (float) ($totalsByDay[$dateString]->remaining_value ?? 0);
        }

        return response()->json([
            'labels' => $labels,
            'requested' => $requested,
            'approved' => $approved,
            'remaining' => $remaining,
        ]);
    }
}
