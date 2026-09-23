<?php

namespace App\Domain\Checkout\Contracts;

use App\Domain\Checkout\ValueObjects\Money;

// A discount that depends on the whole basket, not a single SKU.
interface BasketOffer
{
    /**
     * @param  array<string, int>  $counts  SKU => quantity scanned
     */
    public function discount(array $counts, Money $subtotal): Money;
}
