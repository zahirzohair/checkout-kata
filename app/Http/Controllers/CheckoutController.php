<?php

namespace App\Http\Controllers;

use App\Domain\Checkout\Contracts\PricingRuleRepository;
use App\Domain\Checkout\Exceptions\UnknownSkuException;
use App\Http\Requests\ScanItemRequest;
use App\Services\Checkout\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CheckoutService $checkout,
        private readonly PricingRuleRepository $rules,
    ) {}

    /**
     * Show the checkout page.
     */
    public function index(): Response
    {
        $checkout = $this->checkout->current();

        $scanned = [];
        foreach ($checkout->scannedCounts() as $sku => $quantity) {
            $scanned[] = ['sku' => $sku, 'quantity' => $quantity];
        }

        return Inertia::render('Checkout/Index', [
            'skus' => $this->rules->listSkus(),
            'scanned' => $scanned,
            'totalCents' => $checkout->total()->cents(),
        ]);
    }

    /**
     * Scan one item.
     */
    public function scan(ScanItemRequest $request): RedirectResponse
    {
        try {
            $this->checkout->scan($request->validated('sku'));
        } catch (UnknownSkuException $e) {
            throw ValidationException::withMessages(['sku' => $e->getMessage()]);
        }

        return to_route('checkout.index');
    }

    /**
     * Clear the current basket.
     */
    public function reset(): RedirectResponse
    {
        $this->checkout->reset();

        return to_route('checkout.index');
    }
}
