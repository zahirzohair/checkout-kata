<?php

namespace Tests\Unit\Domain\Checkout\Concerns;

use App\Domain\Checkout\Rules\MultiBuyPricingRule;
use App\Domain\Checkout\Rules\UnitPricingRule;
use App\Domain\Checkout\ValueObjects\Money;
use App\Repositories\Checkout\InMemoryPricingRuleRepository;

// The kata's example price table: A and B have specials, C and D don't.
trait UsesKataPriceTable
{
    private function kataRules(): InMemoryPricingRuleRepository
    {
        return new InMemoryPricingRuleRepository([
            'A' => new MultiBuyPricingRule(Money::fromCents(50), specialQuantity: 3, specialPrice: Money::fromCents(130)),
            'B' => new MultiBuyPricingRule(Money::fromCents(30), specialQuantity: 2, specialPrice: Money::fromCents(45)),
            'C' => new UnitPricingRule(Money::fromCents(20)),
            'D' => new UnitPricingRule(Money::fromCents(15)),
        ]);
    }
}
