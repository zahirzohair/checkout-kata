<?php

namespace App\Domain\Checkout;

use App\Domain\Checkout\Contracts\BasketOffer;
use App\Domain\Checkout\Contracts\PricingRuleRepository;
use App\Domain\Checkout\Exceptions\UnknownSkuException;
use App\Domain\Checkout\ValueObjects\Money;
use App\Domain\Checkout\ValueObjects\Sku;

// One checkout transaction: scan items, then read the total.
final class CheckOut
{
    /** @var array<string, int> SKU => quantity scanned */
    private array $counts = [];

    /**
     * @param  array<string, int>  $initialCounts  SKU => quantity already scanned
     * @param  list<BasketOffer>  $offers
     */
    public function __construct(
        private readonly PricingRuleRepository $rules,
        array $initialCounts = [],
        private readonly array $offers = [],
    ) {
        foreach ($initialCounts as $sku => $quantity) {
            $sku = Sku::fromString($sku)->value;

            try {
                $this->rules->ruleFor($sku);
            } catch (UnknownSkuException) {
                continue; // no longer priced — drop it rather than fail to rehydrate
            }

            $this->counts[$sku] = $quantity;
        }
    }

    /**
     * @throws UnknownSkuException
     */
    public function scan(string $sku): void
    {
        $sku = Sku::fromString($sku)->value;
        $this->rules->ruleFor($sku); // throws UnknownSkuException if $sku is not priced

        $this->counts[$sku] = ($this->counts[$sku] ?? 0) + 1;
    }

    public function total(): Money
    {
        $subtotal = Money::zero();

        foreach ($this->counts as $sku => $quantity) {
            $subtotal = $subtotal->add($this->rules->ruleFor($sku)->priceFor($quantity));
        }

        $discount = Money::zero();

        foreach ($this->offers as $offer) {
            $discount = $discount->add($offer->discount($this->counts, $subtotal));
        }

        return $subtotal->subtract($discount);
    }

    /**
     * @return array<string, int> SKU => quantity
     */
    public function scannedCounts(): array
    {
        return $this->counts;
    }
}
