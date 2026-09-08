<?php

namespace Database\Factories;

use App\Domain\Checkout\Enums\PricingStrategy;
use App\Models\SkuPricing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SkuPricing>
 */
class SkuPricingFactory extends Factory
{
    protected $model = SkuPricing::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sku' => strtoupper(fake()->unique()->lexify('?')),
            'label' => fake()->word(),
            'strategy' => PricingStrategy::Unit,
            'unit_price_cents' => fake()->numberBetween(10, 200),
            'special_quantity' => null,
            'special_price_cents' => null,
        ];
    }
}
