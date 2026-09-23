<?php

namespace Tests\Unit\Domain\Checkout\Offers;

use App\Domain\Checkout\Offers\PercentOffBasketWhenSkusPresent;
use App\Domain\Checkout\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

class PercentOffBasketWhenSkusPresentTest extends TestCase
{
    public function test_no_discount_when_a_required_sku_is_missing(): void
    {
        $offer = new PercentOffBasketWhenSkusPresent(['A', 'B'], percent: 10);

        $this->assertSame(0, $offer->discount(['A' => 1], Money::fromCents(50))->cents());
        $this->assertSame(0, $offer->discount(['B' => 1], Money::fromCents(30))->cents());
        $this->assertSame(0, $offer->discount([], Money::fromCents(0))->cents());
    }

    public function test_ten_percent_of_the_whole_subtotal_when_both_skus_are_present(): void
    {
        $offer = new PercentOffBasketWhenSkusPresent(['A', 'B'], percent: 10);

        $this->assertSame(8, $offer->discount(['A' => 1, 'B' => 1], Money::fromCents(80))->cents());
        $this->assertSame(19, $offer->discount(['A' => 3, 'B' => 2, 'D' => 1], Money::fromCents(190))->cents());
    }

    public function test_required_skus_are_normalised_like_checkout_scans(): void
    {
        $offer = new PercentOffBasketWhenSkusPresent(['a', 'b'], percent: 10);

        $this->assertSame(8, $offer->discount(['A' => 1, 'B' => 1], Money::fromCents(80))->cents());
    }

    public function test_it_rejects_an_empty_required_sku_list(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new PercentOffBasketWhenSkusPresent([], percent: 10);
    }
}
