<?php

namespace Tests\Unit\Domain\Checkout\Rules;

use App\Domain\Checkout\Rules\MultiBuyPricingRule;
use App\Domain\Checkout\ValueObjects\Money;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MultiBuyPricingRuleTest extends TestCase
{
    #[DataProvider('quantities')]
    public function test_price_for_quantity_against_the_kata_a_rule(int $quantity, int $expectedPrice): void
    {
        $rule = new MultiBuyPricingRule(Money::fromCents(50), specialQuantity: 3, specialPrice: Money::fromCents(130));

        $this->assertSame($expectedPrice, $rule->priceFor($quantity)->cents());
    }

    /**
     * @return array<string, array{0: int, 1: int}>
     */
    public static function quantities(): array
    {
        return [
            'none scanned' => [0, 0],
            'below the special threshold' => [1, 50],
            'still below the special threshold' => [2, 100],
            'exactly the special threshold' => [3, 130],
            'one group plus a remainder' => [4, 180],
            'one group plus two remainder' => [5, 230],
            'two full groups' => [6, 260],
        ];
    }

    public function test_price_scales_correctly_for_very_large_quantities(): void
    {
        $rule = new MultiBuyPricingRule(Money::fromCents(50), specialQuantity: 3, specialPrice: Money::fromCents(130));

        // 333,333 groups of 3, remainder 1.
        $this->assertSame(333_333 * 130 + 1 * 50, $rule->priceFor(1_000_000)->cents());
    }

    public function test_special_quantity_must_be_at_least_two(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new MultiBuyPricingRule(Money::fromCents(50), specialQuantity: 1, specialPrice: Money::fromCents(40));
    }

    public function test_unit_price_cannot_be_negative(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new MultiBuyPricingRule(Money::fromCents(-1), specialQuantity: 3, specialPrice: Money::fromCents(130));
    }

    public function test_special_price_cannot_be_negative(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new MultiBuyPricingRule(Money::fromCents(50), specialQuantity: 3, specialPrice: Money::fromCents(-1));
    }
}
