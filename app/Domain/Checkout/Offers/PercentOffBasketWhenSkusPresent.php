<?php

namespace App\Domain\Checkout\Offers;

use App\Domain\Checkout\Contracts\BasketOffer;
use App\Domain\Checkout\ValueObjects\Money;
use App\Domain\Checkout\ValueObjects\Sku;

// When every required SKU appears at least once, take a percent off the basket subtotal.
final class PercentOffBasketWhenSkusPresent implements BasketOffer
{
    /** @var list<string> */
    private readonly array $requiredSkus;

    /**
     * @param  list<string>  $requiredSkus
     */
    public function __construct(
        array $requiredSkus,
        private readonly int $percent,
    ) {
        if ($requiredSkus === []) {
            throw new \InvalidArgumentException('At least one required SKU is needed.');
        }

        if ($percent < 1 || $percent > 100) {
            throw new \InvalidArgumentException('Percent must be between 1 and 100.');
        }

        $this->requiredSkus = array_values(array_map(
            static fn (string $sku): string => Sku::fromString($sku)->value,
            $requiredSkus,
        ));
    }

    public function discount(array $counts, Money $subtotal): Money
    {
        foreach ($this->requiredSkus as $sku) {
            if (($counts[$sku] ?? 0) < 1) {
                return Money::zero();
            }
        }

        return $subtotal->percent($this->percent);
    }
}
