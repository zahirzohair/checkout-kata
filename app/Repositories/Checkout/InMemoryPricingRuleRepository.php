<?php

namespace App\Repositories\Checkout;

use App\Domain\Checkout\Contracts\PricingRule;
use App\Domain\Checkout\Contracts\PricingRuleRepository;
use App\Domain\Checkout\Exceptions\UnknownSkuException;
use App\Domain\Checkout\ValueObjects\Sku;

// Pricing rules from a plain array. Used in tests.
final class InMemoryPricingRuleRepository implements PricingRuleRepository
{
    /**
     * @param  array<string, PricingRule>  $rules  keyed by uppercase SKU
     */
    public function __construct(private readonly array $rules) {}

    public function ruleFor(string $sku): PricingRule
    {
        $sku = Sku::fromString($sku)->value;

        return $this->rules[$sku] ?? throw new UnknownSkuException($sku);
    }

    /**
     * @return array<int, array{sku: string, label: string|null}>
     */
    public function listSkus(): array
    {
        return array_map(fn (string $sku) => ['sku' => $sku, 'label' => null], array_keys($this->rules));
    }
}
