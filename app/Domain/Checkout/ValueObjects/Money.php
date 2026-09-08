<?php

namespace App\Domain\Checkout\ValueObjects;

// Money as integer cents — floats lose precision (0.1 + 0.2 !== 0.3 in IEEE-754).
// Never negative: a negative price is always a bug, not a valid business state.
final class Money
{
    private function __construct(private readonly int $cents)
    {
        if ($cents < 0) {
            throw new \InvalidArgumentException('Money cannot be negative.');
        }
    }

    public static function fromCents(int $cents): self
    {
        return new self($cents);
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public function cents(): int
    {
        return $this->cents;
    }

    public function add(Money $other): self
    {
        return new self($this->cents + $other->cents);
    }

    public function multiply(int $quantity): self
    {
        if ($quantity < 0) {
            throw new \InvalidArgumentException('Quantity cannot be negative.');
        }

        return new self($this->cents * $quantity);
    }

    public function equals(Money $other): bool
    {
        return $this->cents === $other->cents;
    }

    // e.g. 130 -> "€1.30". Single currency, no locale — the kata doesn't need more.
    public function format(string $currencySymbol = '€'): string
    {
        return sprintf('%s%s', $currencySymbol, number_format($this->cents / 100, 2));
    }
}
