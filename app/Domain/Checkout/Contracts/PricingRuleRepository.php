<?php

namespace App\Domain\Checkout\Contracts;

use App\Domain\Checkout\Exceptions\UnknownSkuException;

// Finds the PricingRule for a SKU, and lists every SKU it knows about.
interface PricingRuleRepository
{
    /**
     * @throws UnknownSkuException
     */
    public function ruleFor(string $sku): PricingRule;

    /**
     * Every priced SKU, ordered for display — sku and label only, since
     * that's all a caller needs to show "what can I scan?" without asking
     * for a price (use ruleFor() for that).
     *
     * @return array<int, array{sku: string, label: string|null}>
     */
    public function listSkus(): array;
}
