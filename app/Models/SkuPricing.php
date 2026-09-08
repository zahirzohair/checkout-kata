<?php

namespace App\Models;

use App\Domain\Checkout\Enums\PricingStrategy;
use App\Domain\Checkout\ValueObjects\Sku;
use Database\Factories\SkuPricingFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $sku
 * @property string|null $label
 * @property PricingStrategy $strategy
 * @property int $unit_price_cents
 * @property int|null $special_quantity
 * @property int|null $special_price_cents
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class SkuPricing extends Model
{
    /** @use HasFactory<SkuPricingFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'sku',
        'label',
        'strategy',
        'unit_price_cents',
        'special_quantity',
        'special_price_cents',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'strategy' => PricingStrategy::class,
            'unit_price_cents' => 'integer',
            'special_quantity' => 'integer',
            'special_price_cents' => 'integer',
        ];
    }

    /**
     * @return Attribute<string, string>
     */
    protected function sku(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => Sku::fromString($value)->value,
        );
    }
}
