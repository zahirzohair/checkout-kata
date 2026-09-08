<?php

namespace Tests\Unit\Domain\Checkout;

use App\Domain\Checkout\CheckOut;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Domain\Checkout\Concerns\UsesKataPriceTable;

// Checks every basket total from the kata spec.
class CheckOutPricingTest extends TestCase
{
    use UsesKataPriceTable;

    #[DataProvider('baskets')]
    public function test_total_for_basket(string $basket, int $expectedTotal): void
    {
        $checkout = new CheckOut($this->kataRules());

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
            'single A' => ['A', 50],
            'A then B' => ['AB', 80],
            'one of each' => ['CDBA', 115],
            'two As, below special' => ['AA', 100],
            'three As, exactly the special' => ['AAA', 130],
            'four As, special plus one' => ['AAAA', 180],
            'five As, special plus two' => ['AAAAA', 230],
            'six As, two full specials' => ['AAAAAA', 260],
            'three As plus one B' => ['AAAB', 160],
            'three As plus special B pair' => ['AAABB', 175],
            'three As, special B pair, one D' => ['AAABBD', 190],
            'same basket scanned out of order' => ['DABABA', 190],
        ];
    }
}
