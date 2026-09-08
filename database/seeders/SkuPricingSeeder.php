<?php

namespace Database\Seeders;

use App\Domain\Checkout\Enums\PricingStrategy;
use App\Models\SkuPricing;
use Illuminate\Database\Seeder;

class SkuPricingSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            ['sku' => 'A', 'label' => 'Item A', 'strategy' => PricingStrategy::MultiBuy, 'unit_price_cents' => 50, 'special_quantity' => 3, 'special_price_cents' => 130],
            ['sku' => 'B', 'label' => 'Item B', 'strategy' => PricingStrategy::MultiBuy, 'unit_price_cents' => 30, 'special_quantity' => 2, 'special_price_cents' => 45],
            ['sku' => 'C', 'label' => 'Item C', 'strategy' => PricingStrategy::Unit, 'unit_price_cents' => 20, 'special_quantity' => null, 'special_price_cents' => null],
            ['sku' => 'D', 'label' => 'Item D', 'strategy' => PricingStrategy::Unit, 'unit_price_cents' => 15, 'special_quantity' => null, 'special_price_cents' => null],
        ];

        foreach ($rules as $rule) {
            SkuPricing::query()->updateOrCreate(['sku' => $rule['sku']], $rule);
        }
    }
}
