<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequests;
use Illuminate\Http\Request;

class PurchasesSummaryController extends Controller
{
    public function __invoke(Request $request)
    {
        $tab = $request->string('tab')->toString() ?: 'type';

        $column = match ($tab) {
            'type' => 'type_name',
            'payment' => 'payment_type',
            'cbl' => 'state_code',
            default => 'type_name',
        };

        $query = PurchaseRequests::query()
            ->where('is_deleted', 0)
            ->whereNotNull($column)
            ->where($column, '!=', '');

        if ($tab === 'type') {
            $labelExpr = "
        CASE
            WHEN LOWER(TRIM($column)) IN ('Card', 'بطاقة') THEN 'بطاقة'
            ELSE $column
        END
        ";
        } else {
            $labelExpr = $column;
        }

        $rows = $query->selectRaw("$labelExpr AS label, COUNT(*) AS value")
            ->groupBy('label')
            ->orderByDesc('value')
            ->get();

        return response()->json([
            'tab' => $tab,
            'column' => $column,
            'data' => $rows,
        ]);
    }
}
