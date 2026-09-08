<?php

namespace Tests\Feature;

use App\Domain\Checkout\Enums\PricingStrategy;
use App\Models\SkuPricing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Raw insert(), not Eloquent create() — bypasses casts, so the
        // strategy column needs its raw backing string, not the enum itself.
        SkuPricing::query()->insert([
            ['sku' => 'A', 'label' => 'Item A', 'strategy' => PricingStrategy::MultiBuy->value, 'unit_price_cents' => 50, 'special_quantity' => 3, 'special_price_cents' => 130],
            ['sku' => 'B', 'label' => 'Item B', 'strategy' => PricingStrategy::MultiBuy->value, 'unit_price_cents' => 30, 'special_quantity' => 2, 'special_price_cents' => 45],
            ['sku' => 'C', 'label' => 'Item C', 'strategy' => PricingStrategy::Unit->value, 'unit_price_cents' => 20, 'special_quantity' => null, 'special_price_cents' => null],
            ['sku' => 'D', 'label' => 'Item D', 'strategy' => PricingStrategy::Unit->value, 'unit_price_cents' => 15, 'special_quantity' => null, 'special_price_cents' => null],
        ]);
    }

    public function test_a_fresh_checkout_page_has_no_items_and_a_zero_total(): void
    {
        $response = $this->get(route('checkout.index'));

        $response->assertOk();
        $response->assertInertia(
            fn($page) => $page
                ->component('Checkout/Index')
                ->where('totalCents', 0)
                ->where('scanned', [])
        );
    }

    public function test_the_checkout_page_shows_every_sku_with_its_label(): void
    {
        $response = $this->get(route('checkout.index'));

        $response->assertInertia(
            fn($page) => $page
                ->has('skus', 4)
                ->where('skus.0', ['sku' => 'A', 'label' => 'Item A'])
        );
    }

    public function test_the_checkout_page_queries_sku_pricings_only_once(): void
    {
        // A non-empty basket is what used to trigger a second query: the
        // controller's own listSkus() read, plus the repository's separate
        // load while validating the scanned item. Seeding the session
        // directly (rather than via a prior POST) keeps this to exactly one
        // real HTTP request, which is what "once per request" is about.
        $queries = 0;
        DB::listen(function ($query) use (&$queries) {
            if (str_contains($query->sql, 'sku_pricings')) {
                $queries++;
            }
        });

        $this->withSession(['checkout.scanned' => ['A' => 1]])
            ->get(route('checkout.index'))
            ->assertOk();

        $this->assertSame(1, $queries, 'Expected exactly one query against sku_pricings.');
    }

    public function test_scanning_a_basket_reaches_the_expected_total(): void
    {
        foreach (str_split('AAABBD') as $sku) {
            $this->post(route('checkout.scan'), ['sku' => $sku])->assertRedirect(route('checkout.index'));
        }

        $response = $this->get(route('checkout.index'));

        $response->assertInertia(fn($page) => $page->where('totalCents', 190));
    }

    public function test_scanning_an_unknown_sku_is_rejected_and_does_not_change_the_total(): void
    {
        $this->post(route('checkout.scan'), ['sku' => 'A'])->assertRedirect(route('checkout.index'));

        $response = $this->post(route('checkout.scan'), ['sku' => 'Z']);

        $response->assertSessionHasErrors('sku');

        $this->get(route('checkout.index'))->assertInertia(fn($page) => $page->where('totalCents', 50));
    }

    public function test_a_sku_removed_after_being_scanned_does_not_crash_the_page(): void
    {
        $this->post(route('checkout.scan'), ['sku' => 'A']);
        $this->post(route('checkout.scan'), ['sku' => 'B']);

        SkuPricing::query()->where('sku', 'A')->delete();

        // A real request gets a fresh PricingRuleRepository (and so a fresh
        // query); this test simulates several requests in one method, so it
        // has to drop the scoped instance itself to get the same guarantee.
        $this->app->forgetScopedInstances();

        $response = $this->get(route('checkout.index'));

        $response->assertOk();
        $response->assertInertia(fn($page) => $page->where('totalCents', 30));
    }

    public function test_scan_and_reset_stay_locked_against_concurrent_requests_on_the_same_session(): void
    {
        // Real concurrency can't be simulated inside a single-process test
        // so this locks in the configuration instead:
        // if ->block() is ever removed from these routes, this fails loudly
        // rather than the basket silently losing scans under real load.
        $this->assertNotNull(
            Route::getRoutes()->getByName('checkout.scan')->locksFor(),
            'checkout.scan must stay session-locked, or concurrent scans can race and lose one.'
        );

        $this->assertNotNull(
            Route::getRoutes()->getByName('checkout.reset')->locksFor(),
            'checkout.reset must stay session-locked, or a reset can race with a scan.'
        );
    }

    public function test_reset_clears_the_current_basket(): void
    {
        $this->post(route('checkout.scan'), ['sku' => 'A']);
        $this->post(route('checkout.reset'))->assertRedirect(route('checkout.index'));

        $response = $this->get(route('checkout.index'));

        $response->assertInertia(
            fn($page) => $page
                ->where('totalCents', 0)
                ->where('scanned', [])
        );
    }
}
