<?php

namespace App\Domain\Checkout;

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
     */
    public function __construct(
        private readonly PricingRuleRepository $rules,
        array $initialCounts = [],
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
        $total = Money::zero();

        foreach ($this->counts as $sku => $quantity) {
            $total = $total->add($this->rules->ruleFor($sku)->priceFor($quantity));
        }

        return $total;
    }

    /**
     * @return array<string, int> SKU => quantity
     */
    public function scannedCounts(): array
    {
        return $this->counts;
    }
}
