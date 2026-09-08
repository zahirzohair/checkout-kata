<?php

namespace Tests\Unit\Domain\Checkout\Rules;

use App\Domain\Checkout\Rules\UnitPricingRule;
use App\Domain\Checkout\ValueObjects\Money;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class UnitPricingRuleTest extends TestCase
{
    #[DataProvider('quantities')]
    public function test_price_is_quantity_times_unit_price(int $quantity, int $expectedPrice): void
    {
        $rule = new UnitPricingRule(Money::fromCents(20));

        $this->assertSame($expectedPrice, $rule->priceFor($quantity)->cents());
    }

    /**
     * @return array<string, array{0: int, 1: int}>
     */
    public static function quantities(): array
    {
        return [
            'none scanned' => [0, 0],
            'one scanned' => [1, 20],
            'five scanned' => [5, 100],
        ];
    }

    public function test_unit_price_cannot_be_negative(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new UnitPricingRule(Money::fromCents(-1));
    }
}
