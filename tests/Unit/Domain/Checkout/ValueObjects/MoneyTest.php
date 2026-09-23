<?php

namespace Tests\Unit\Domain\Checkout\ValueObjects;

use App\Domain\Checkout\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_zero_is_zero_cents(): void
    {
        $this->assertSame(0, Money::zero()->cents());
    }

    public function test_it_cannot_be_negative(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Money::fromCents(-1);
    }

    public function test_add_sums_both_amounts(): void
    {
        $total = Money::fromCents(50)->add(Money::fromCents(30));

        $this->assertSame(80, $total->cents());
    }

    public function test_subtract_removes_the_other_amount(): void
    {
        $this->assertSame(70, Money::fromCents(100)->subtract(Money::fromCents(30))->cents());
    }

    public function test_subtract_rejects_going_negative(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Money::fromCents(10)->subtract(Money::fromCents(11));
    }

    public function test_multiply_scales_by_quantity(): void
    {
        $this->assertSame(150, Money::fromCents(50)->multiply(3)->cents());
        $this->assertSame(0, Money::fromCents(50)->multiply(0)->cents());
    }

    public function test_percent_floors_to_whole_cents(): void
    {
        $this->assertSame(8, Money::fromCents(80)->percent(10)->cents());
        $this->assertSame(11, Money::fromCents(115)->percent(10)->cents());
    }

    public function test_percent_rejects_out_of_range_values(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Money::fromCents(100)->percent(101);
    }

    public function test_multiply_rejects_a_negative_quantity(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Money::fromCents(50)->multiply(-1);
    }

    public function test_equals_compares_by_value(): void
    {
        $this->assertTrue(Money::fromCents(50)->equals(Money::fromCents(50)));
        $this->assertFalse(Money::fromCents(50)->equals(Money::fromCents(51)));
    }

    public function test_format_renders_cents_as_a_dollar_amount(): void
    {
        $this->assertSame('$1.30', Money::fromCents(130)->format());
        $this->assertSame('$0.05', Money::fromCents(5)->format());
        $this->assertSame('€1.30', Money::fromCents(130)->format('€'));
    }
}
