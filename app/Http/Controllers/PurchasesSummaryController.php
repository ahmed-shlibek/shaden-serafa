<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequests;
use Illuminate\Http\Request;

class PurchasesSummaryController extends Controller
{
    public function __invoke(Request $request)
    {
        $tab = $request->string('tab')->toString();
        $tab = in_array($tab, ['type', 'payment', 'cbl'], true) ? $tab : 'type';

        $column = match ($tab) {
            'type' => 'type_name',
            'payment' => 'payment_type',
            'cbl' => 'state_code',
        };

        $query = PurchaseRequests::query()
            ->where('is_deleted', 0)
            ->whereNotNull($column)
            ->where($column, '!=', '');

        $colSql = "`{$column}`";

        if ($tab === 'type') {
            $labelExpr = "
                CASE
                    WHEN TRIM($colSql) IN ('Card', 'بطاقة') THEN 'بطاقة'
                    ELSE $colSql
                END
        ";
        } else {
            $labelExpr = $colSql;
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
