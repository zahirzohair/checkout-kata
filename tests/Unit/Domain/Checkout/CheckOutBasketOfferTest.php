<?php

namespace Tests\Unit\Domain\Checkout;

use App\Domain\Checkout\CheckOut;
use App\Domain\Checkout\Offers\PercentOffBasketWhenSkusPresent;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Domain\Checkout\Concerns\UsesKataPriceTable;

// Baskets with A and B get 10% off the whole subtotal (after per-SKU specials).
class CheckOutBasketOfferTest extends TestCase
{
    use UsesKataPriceTable;

    #[DataProvider('baskets')]
    public function test_total_for_basket_with_ab_percent_offer(string $basket, int $expectedTotal): void
    {
        $checkout = new CheckOut($this->kataRules(), offers: [
            new PercentOffBasketWhenSkusPresent(['A', 'B'], percent: 10),
        ]);

        foreach (str_split($basket) as $sku) {
            $checkout->scan($sku);
        }

        $this->assertSame($expectedTotal, $checkout->total()->cents());
    }

    /**
     * @return array<string, array{0: string, 1: int}>
     */
    public static function baskets(): array
    {
        return [
            'empty basket' => ['', 0],
            'A only — no offer' => ['A', 50],
            'B only — no offer' => ['B', 30],
            'A and B — 10% of 80' => ['AB', 72],
            'one of each — 10% of 115' => ['CDBA', 104],
            'three As plus one B — 10% of 160' => ['AAAB', 144],
            'three As, special B pair, one D — 10% of 190' => ['AAABBD', 171],
            'same basket scanned out of order' => ['DABABA', 171],
        ];
    }
}
