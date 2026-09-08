<?php

namespace Tests\Unit\Repositories\Checkout;

use App\Domain\Checkout\Enums\PricingStrategy;
use App\Domain\Checkout\Rules\MultiBuyPricingRule;
use App\Domain\Checkout\Rules\UnitPricingRule;
use App\Models\SkuPricing;
use App\Repositories\Checkout\PricingRuleFactory;
use Tests\TestCase;

// Needs a real SkuPricing model, so this uses the Laravel test case.
class PricingRuleFactoryTest extends TestCase
{
    public function test_makes_a_unit_pricing_rule_for_the_unit_strategy(): void
    {
        $record = SkuPricing::factory()->make([
            'strategy' => PricingStrategy::Unit,
            'unit_price_cents' => 20,
        ]);

        $rule = (new PricingRuleFactory)->make($record);

        $this->assertInstanceOf(UnitPricingRule::class, $rule);
        $this->assertSame(60, $rule->priceFor(3)->cents());
    }

    public function test_makes_a_multi_buy_pricing_rule_for_the_multi_buy_strategy(): void
    {
        $record = SkuPricing::factory()->make([
            'strategy' => PricingStrategy::MultiBuy,
            'unit_price_cents' => 50,
            'special_quantity' => 3,
            'special_price_cents' => 130,
        ]);

        $rule = (new PricingRuleFactory)->make($record);

        $this->assertInstanceOf(MultiBuyPricingRule::class, $rule);
        $this->assertSame(130, $rule->priceFor(3)->cents());
    }
}
