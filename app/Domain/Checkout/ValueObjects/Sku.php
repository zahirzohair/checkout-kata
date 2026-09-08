<?php

namespace App\Domain\Checkout\ValueObjects;

// A SKU, always in its canonical (uppercase) form.
final class Sku
{
    private function __construct(public readonly string $value) {}

    public static function fromString(string $value): self
    {
        return new self(strtoupper($value));
    }
}
