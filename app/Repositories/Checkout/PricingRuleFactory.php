<?php

namespace App\Repositories\Checkout;

use App\Domain\Checkout\Contracts\PricingRule;
use App\Domain\Checkout\Enums\PricingStrategy;
use App\Domain\Checkout\Rules\MultiBuyPricingRule;
use App\Domain\Checkout\Rules\UnitPricingRule;
use App\Domain\Checkout\ValueObjects\Money;
use App\Models\SkuPricing;

// Builds a PricingRule from a saved SkuPricing row.
final class PricingRuleFactory
{
    public function make(SkuPricing $record): PricingRule
    {
        return match ($record->strategy) {
            PricingStrategy::Unit => new UnitPricingRule(
                Money::fromCents($record->unit_price_cents),
            ),
            PricingStrategy::MultiBuy => new MultiBuyPricingRule(
                Money::fromCents($record->unit_price_cents),
                $record->special_quantity,
                Money::fromCents($record->special_price_cents),
            ),
        };
    }
}
