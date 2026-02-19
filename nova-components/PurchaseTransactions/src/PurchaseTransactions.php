<?php

namespace ShadenSerafa\PurchaseTransactions;

use Laravel\Nova\Card;

class PurchaseTransactions extends Card
{
    /**
     * The width of the card (1/3, 1/2, or full).
     *
     * @var string
     */
    public $width = '1/3';

    /**
     * Get the component name for the element.
     */
    public function component(): string
    {
        return 'purchase-transactions';
    }
}
