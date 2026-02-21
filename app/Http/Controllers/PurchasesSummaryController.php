<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequests;
use Illuminate\Http\Request;

class PurchasesSummaryController extends Controller
{
    /**
     * Allowed tab → column mappings (safe, no user input in SQL).
     */
    private const TAB_COLUMNS = [
        'type' => 'type_name',
        'payment' => 'payment_type',
        'cbl' => 'state_code',
    ];

    public function __invoke(Request $request)
    {
        $tab = $request->string('tab')->toString();
        $tab = array_key_exists($tab, self::TAB_COLUMNS) ? $tab : 'type';

        $column = self::TAB_COLUMNS[$tab];

        $query = PurchaseRequests::query()
            ->active()
            ->whereNotNull($column)
            ->where($column, '!=', '');

        $labelExpr = $tab === 'type'
            ? "CASE
                    WHEN TRIM(`{$column}`) IN ('Card', 'بطاقة') THEN 'Card'
                    WHEN TRIM(`{$column}`) = 'الإيداع بحساب العملة الأجنبية' THEN 'Deposit in foreign currency account'
                    ELSE `{$column}`
                END"
            : "`{$column}`";

        $rows = $query->selectRaw("{$labelExpr} AS label, COUNT(*) AS value")
            ->groupBy('label')
            ->orderByDesc('value')
            ->get()
            ->map(fn ($row) => [
                'label' => __($row->label),
                'value' => $row->value,
            ]);

        return response()->json([
            'tab' => $tab,
            'column' => $column,
            'data' => $rows,
        ]);
    }
}
