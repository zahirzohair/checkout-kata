<?php

namespace Tests\Unit\Services\Checkout;

use App\Domain\Checkout\Enums\PricingStrategy;
use App\Domain\Checkout\Exceptions\UnknownSkuException;
use App\Models\SkuPricing;
use App\Services\Checkout\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_fresh_checkout_totals_zero(): void
    {
        $service = app(CheckoutService::class);

        $this->assertSame(0, $service->current()->total()->cents());
    }

    public function test_scanning_an_item_is_remembered_between_calls(): void
    {
        SkuPricing::query()->create(['sku' => 'A', 'strategy' => PricingStrategy::Unit, 'unit_price_cents' => 50]);

        app(CheckoutService::class)->scan('A');

        $this->assertSame(50, app(CheckoutService::class)->current()->total()->cents());
    }

    public function test_scanning_an_unknown_sku_throws(): void
    {
        $this->expectException(UnknownSkuException::class);

        app(CheckoutService::class)->scan('Z');
    }

    public function test_reset_clears_the_basket(): void
    {
        SkuPricing::query()->create(['sku' => 'A', 'strategy' => PricingStrategy::Unit, 'unit_price_cents' => 50]);
        $service = app(CheckoutService::class);
        $service->scan('A');

        $service->reset();

        $this->assertSame(0, $service->current()->total()->cents());
    }
}
