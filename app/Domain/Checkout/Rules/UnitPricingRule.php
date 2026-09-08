<?php

namespace App\Domain\Checkout\Rules;

use App\Domain\Checkout\Contracts\PricingRule;
use App\Domain\Checkout\ValueObjects\Money;

// Flat price per unit, no special.
final class UnitPricingRule implements PricingRule
{
    public function __construct(private readonly Money $unitPrice) {}

    public function priceFor(int $quantity): Money
    {
        return $this->unitPrice->multiply($quantity);
    }
}
