# Supermarket Checkout Kata

A small checkout app. You scan items, and it adds up the total price.

Some items have a special price. For example: item A costs 50 cents each,
but if you buy 3 of them, all 3 together cost $1.30.

Built with **Laravel**, **Inertia**, **Vue 3** (TypeScript), and **Tailwind CSS**.

## What's in the box

- A **checkout page** — click item buttons to "scan" them and watch the total update.

## Price list

| Item | Normal price | Special     |
| ---- | ------------ | ----------- |
| A    | 50¢          | 3 for $1.30 |
| B    | 30¢          | 2 for 45¢   |
| C    | 20¢          | none        |
| D    | 15¢          | none        |

These prices are set by the database seeder (see `database/seeders/SkuPricingSeeder.php`).

## Setup

You need PHP 8.2+, Composer, and Node.js 18+ installed.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
```

## Run it

```bash
composer run dev
```

This starts everything you need. Then open `http://localhost:8000/checkout` in your browser.

## Run the tests

```bash
php artisan test      # backend tests
npm run test          # frontend tests
npm run types:check   # TypeScript check
```

## Project structure

Only the files specific to this checkout kata are listed (a normal Laravel
app has more files around these).

```
checkout-kata/
├── app/
│   ├── Domain/Checkout/               # pure PHP — no database, no framework
│   │   ├── CheckOut.php               # scans items and totals the price
│   │   ├── Contracts/
│   │   │   ├── PricingRule.php           # shared shape for every price rule
│   │   │   └── PricingRuleRepository.php # how CheckOut finds a rule
│   │   ├── Rules/
│   │   │   ├── UnitPricingRule.php       # normal price per item
│   │   │   └── MultiBuyPricingRule.php   # e.g. "3 for $1.30"
│   │   ├── ValueObjects/
│   │   │   ├── Sku.php                # makes every SKU uppercase, in one place
│   │   │   └── Money.php              # cents as an int, never negative, never a float
│   │   ├── Enums/
│   │   │   └── PricingStrategy.php    # which PricingRule a SkuPricing row builds
│   │   └── Exceptions/
│   │       └── UnknownSkuException.php   # scanned item has no price
│   ├── Repositories/Checkout/         # every way of loading pricing rules
│   │   ├── EloquentPricingRuleRepository.php # loads rules from the database
│   │   ├── InMemoryPricingRuleRepository.php # loads rules from a plain list (tests)
│   │   └── PricingRuleFactory.php     # turns a database row into a price rule
│   ├── Services/Checkout/
│   │   └── CheckoutService.php        # keeps the scanned basket in the session
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── CheckoutController.php    # page, scan, and reset — no logic of its own
│   │   └── Requests/
│   │       └── ScanItemRequest.php       # checks the scanned SKU
│   └── Models/
│       └── SkuPricing.php                # one row = one item's price
├── database/
│   ├── migrations/..._create_sku_pricings_table.php
│   ├── factories/SkuPricingFactory.php
│   └── seeders/SkuPricingSeeder.php      # starting prices A–D
├── resources/js/
│   ├── Pages/Checkout/
│   │   ├── Index.vue                     # checkout page
│   │   └── Index.spec.ts                 # component test
│   └── types/checkout.ts                 # shared TypeScript types
├── routes/web.php                        # app URLs
└── tests/
    ├── Unit/Domain/Checkout/             # pricing logic tests, no database needed
    │   ├── Rules/                        # tests for each price rule
    │   └── ValueObjects/
    ├── Unit/Repositories/Checkout/       # tests that do need the database
    ├── Unit/Services/Checkout/           # tests for the session-backed service
    └── Feature/CheckoutFlowTest.php      # full page/flow tests
```

`Domain/Checkout/` only ever holds the business rules and the `Contracts/`
they're built against — nothing in it imports Eloquent or anything else from
the framework, so it can be tested and reasoned about entirely on its own.

`app/Repositories/Checkout/` holds every way of actually finding a pricing
rule for a SKU: the real one (`EloquentPricingRuleRepository`, backed by the
database) and the fake one used in tests (`InMemoryPricingRuleRepository`).
They both live together because they're both implementations of the same
`PricingRuleRepository` contract — which one is "real" doesn't matter to
where it lives. `PricingRuleFactory` sits alongside them because it's the one
piece that knows both "how the database stores a price" and "which
`PricingRule` class that becomes" — `CheckOut` never sees any of this, it
only ever talks to `Contracts/`. So adding a new offer later (like buy one
get one free) means adding one new file to `Rules/` and teaching the factory
about it — nothing else changes.

`CheckoutService` is where the checkout basket actually lives between
requests: it rebuilds a `CheckOut` from whatever's in the session, and saves
it back after every scan. It's injected straight into `CheckoutController`,
so the controller itself has no business logic left — it just takes the
request in, calls the service, and decides what page or redirect to send
back. That split means the same scan/reset behavior could be tested (see
`CheckoutServiceTest`) or reused (a future API endpoint, a console command)
without touching HTTP at all.
