<?php

namespace App\Domain\Checkout\Rules;

use App\Domain\Checkout\Contracts\PricingRule;
use App\Domain\Checkout\ValueObjects\Money;

// "Buy N for Y" pricing. Leftover units are priced at the unit price.
final class MultiBuyPricingRule implements PricingRule
{
    public function __construct(
        private readonly Money $unitPrice,
        private readonly int $specialQuantity,
        private readonly Money $specialPrice,
    ) {
        if ($specialQuantity < 2) {
            throw new \InvalidArgumentException('specialQuantity must be at least 2.');
        }
    }

    public function priceFor(int $quantity): Money
    {
        $groups = intdiv($quantity, $this->specialQuantity);
        $remainder = $quantity % $this->specialQuantity;

        return $this->specialPrice->multiply($groups)->add($this->unitPrice->multiply($remainder));
    }
}
