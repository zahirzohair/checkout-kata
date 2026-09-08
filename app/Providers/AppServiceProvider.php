<?php

namespace App\Providers;

use App\Domain\Checkout\Contracts\PricingRuleRepository;
use App\Repositories\Checkout\EloquentPricingRuleRepository;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // scoped(), not bind(): CheckoutController and CheckoutService both
        // depend on this directly, and they need to share one instance (and
        // so one cached query) within a request, not query separately.
        $this->app->scoped(PricingRuleRepository::class, EloquentPricingRuleRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
