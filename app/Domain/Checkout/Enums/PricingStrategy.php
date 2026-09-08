<?php

namespace App\Domain\Checkout\Enums;

// Which PricingRule a SkuPricing row builds. Plain PHP — no framework import needed.
enum PricingStrategy: string
{
    case Unit = 'unit';
    case MultiBuy = 'multi_buy';
}
