<?php

namespace App\Domain\Checkout\Exceptions;

final class UnknownSkuException extends \DomainException
{
    public function __construct(public readonly string $sku)
    {
        parent::__construct("Unknown SKU: {$sku}");
    }
}
