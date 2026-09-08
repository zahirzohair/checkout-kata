<?php

namespace Tests\Unit\Domain\Checkout;

use App\Domain\Checkout\CheckOut;
use App\Domain\Checkout\Exceptions\UnknownSkuException;
use App\Domain\Checkout\Rules\UnitPricingRule;
use App\Domain\Checkout\ValueObjects\Money;
use App\Repositories\Checkout\InMemoryPricingRuleRepository;
use PHPUnit\Framework\TestCase;

class CheckOutTest extends TestCase
{
    public function test_a_fresh_checkout_totals_zero(): void
    {
        $checkout = new CheckOut($this->rules());

        $this->assertSame(0, $checkout->total()->cents());
    }

    public function test_scanning_an_unknown_sku_throws(): void
    {
        $checkout = new CheckOut($this->rules());

        $this->expectException(UnknownSkuException::class);
        $this->expectExceptionMessage('Unknown SKU: Z');

        $checkout->scan('Z');
    }

    public function test_an_unknown_scan_does_not_change_the_total(): void
    {
        $checkout = new CheckOut($this->rules());
        $checkout->scan('A');

        try {
            $checkout->scan('Z');
        } catch (UnknownSkuException) {
            // expected
        }

        $this->assertSame(50, $checkout->total()->cents());
    }

    public function test_sku_lookup_is_case_insensitive(): void
    {
        $checkout = new CheckOut($this->rules());

        $checkout->scan('a');

        $this->assertSame(50, $checkout->total()->cents());
    }

    public function test_scanned_counts_can_seed_a_new_checkout(): void
    {
        $checkout = new CheckOut($this->rules(), ['A' => 2]);

        $this->assertSame(100, $checkout->total()->cents());

        $checkout->scan('A');

        $this->assertSame(150, $checkout->total()->cents());
    }

    public function test_seeded_counts_for_skus_that_are_no_longer_priced_are_dropped(): void
    {
        $checkout = new CheckOut($this->rules(), ['A' => 2, 'Z' => 5]);

        $this->assertSame(100, $checkout->total()->cents());
        $this->assertSame(['A' => 2], $checkout->scannedCounts());
    }

    private function rules(): InMemoryPricingRuleRepository
    {
        return new InMemoryPricingRuleRepository([
            'A' => new UnitPricingRule(Money::fromCents(50)),
        ]);
    }
}
