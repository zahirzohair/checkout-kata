<?php

namespace Tests\Unit\Domain\Checkout;

use App\Domain\Checkout\CheckOut;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Domain\Checkout\Concerns\UsesKataPriceTable;

class CheckOutIncrementalTest extends TestCase
{
    use UsesKataPriceTable;

    public function test_total_is_correct_after_each_scan(): void
    {
        $checkout = new CheckOut($this->kataRules());

        $this->assertSame(0, $checkout->total()->cents());

        $checkout->scan('A');
        $this->assertSame(50, $checkout->total()->cents());

        $checkout->scan('B');
        $this->assertSame(80, $checkout->total()->cents());

        $checkout->scan('A');
        $this->assertSame(130, $checkout->total()->cents());

        $checkout->scan('A');
        $this->assertSame(160, $checkout->total()->cents());

        $checkout->scan('B');
        $this->assertSame(175, $checkout->total()->cents());
    }
}
