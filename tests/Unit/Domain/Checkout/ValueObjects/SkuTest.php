<?php

namespace Tests\Unit\Domain\Checkout\ValueObjects;

use App\Domain\Checkout\ValueObjects\Sku;
use PHPUnit\Framework\TestCase;

class SkuTest extends TestCase
{
    public function test_it_uppercases_the_value(): void
    {
        $this->assertSame('A', Sku::fromString('a')->value);
        $this->assertSame('A', Sku::fromString('A')->value);
    }
}
