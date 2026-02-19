<?php

namespace App\Nova\Dashboards;

use Laravel\Nova\Dashboards\Main as Dashboard;
use ShadenSerafa\PurchasesSummary\PurchasesSummary;
use ShadenSerafa\PurchaseTransactions\PurchaseTransactions;
use ShadenSerafa\PurchaseValue\PurchaseValue;

class Main extends Dashboard
{
    /**
     * Get the cards for the dashboard.
     *
     * @return array<int, \Laravel\Nova\Card>
     */
    public function cards(): array
    {
        return [
            (new PurchasesSummary)->width('1/3'),
            (new PurchaseValue)->width('2/3'),
            (new PurchaseTransactions)->width('full'),
        ];
    }
}
