<?php

namespace App\Repositories\Checkout;

use App\Domain\Checkout\Contracts\PricingRule;
use App\Domain\Checkout\Contracts\PricingRuleRepository;
use App\Domain\Checkout\Exceptions\UnknownSkuException;
use App\Domain\Checkout\ValueObjects\Sku;
use App\Models\SkuPricing;
use Illuminate\Support\Collection;

// Loads pricing rules from the sku_pricings table.
final class EloquentPricingRuleRepository implements PricingRuleRepository
{
    /** @var Collection<int, SkuPricing>|null */
    private ?Collection $records = null;

    public function __construct(private readonly PricingRuleFactory $factory) {}

    public function ruleFor(string $sku): PricingRule
    {
        $sku = Sku::fromString($sku)->value;

        $record = $this->records()->firstWhere('sku', $sku);

        return $record ? $this->factory->make($record) : throw new UnknownSkuException($sku);
    }

    /**
     * @return array<int, array{sku: string, label: string|null}>
     */
    public function listSkus(): array
    {
        return $this->records()
            ->map(fn(SkuPricing $record) => ['sku' => $record->sku, 'label' => $record->label])
            ->all();
    }

    /**
     * @return Collection<int, SkuPricing>
     */
    private function records(): Collection
    {
        // Cached per instance so scan()'s validation lookup, total()'s pricing
        // lookups, and listSkus()'s display list all share one query instead
        // of each hitting the database separately.
        return $this->records ??= SkuPricing::query()->orderBy('sku')->get();
    }
}
