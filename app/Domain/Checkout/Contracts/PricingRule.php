<?php

namespace App\Domain\Checkout\Contracts;

use App\Domain\Checkout\ValueObjects\Money;

// A pricing strategy for one SKU.
interface PricingRule
{
    // Price for this many scanned units.
    public function priceFor(int $quantity): Money;
}
