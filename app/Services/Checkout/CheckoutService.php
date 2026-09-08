<?php

namespace App\Services\Checkout;

use App\Domain\Checkout\CheckOut;
use App\Domain\Checkout\Contracts\PricingRuleRepository;
use App\Domain\Checkout\Exceptions\UnknownSkuException;
use Illuminate\Contracts\Session\Session;

// Keeps one checkout's scanned items in the session, between requests.
final class CheckoutService
{
    private const SESSION_KEY = 'checkout.scanned';

    public function __construct(
        private readonly PricingRuleRepository $rules,
        private readonly Session $session,
    ) {}

    public function current(): CheckOut
    {
        return new CheckOut($this->rules, $this->scannedCounts());
    }

    /**
     * @throws UnknownSkuException
     */
    public function scan(string $sku): void
    {
        $checkout = $this->current();
        $checkout->scan($sku);

        $this->session->put(self::SESSION_KEY, $checkout->scannedCounts());
    }

    public function reset(): void
    {
        $this->session->forget(self::SESSION_KEY);
    }

    /**
     * @return array<string, int>
     */
    private function scannedCounts(): array
    {
        return $this->session->get(self::SESSION_KEY, []);
    }
}
