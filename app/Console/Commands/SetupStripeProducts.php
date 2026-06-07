<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Stripe\StripeClient;
use Throwable;

class SetupStripeProducts extends Command
{
    protected $signature = 'stripe:setup-products';

    protected $description = 'Create the XploreSmithers Pro product + monthly/annual prices in Stripe and print the price IDs for .env';

    public function handle(): int
    {
        $secret = (string) config('services.stripe.secret');

        if (! $secret || str_contains($secret, 'PLACEHOLDER')) {
            $this->error('STRIPE_SECRET is not set yet. Add your Stripe secret key to .env, run `php artisan config:clear`, then re-run this command.');

            return self::FAILURE;
        }

        $stripe = new StripeClient($secret);
        $mode = str_starts_with($secret, 'sk_live_') ? 'LIVE' : 'TEST';
        $this->info("Using Stripe in {$mode} mode.");

        try {
            $product = collect($stripe->products->all(['limit' => 100, 'active' => true])->data)
                ->first(fn ($p): bool => ($p->metadata['xs_key'] ?? null) === 'pro');

            if (! $product) {
                $product = $stripe->products->create([
                    'name' => 'XploreSmithers Pro',
                    'description' => 'Offline maps, unique points of interest, Pro video content, and GPX downloads.',
                    'metadata' => ['xs_key' => 'pro'],
                ]);
                $this->info("Created product {$product->id}");
            } else {
                $this->info("Reusing existing product {$product->id}");
            }

            $monthly = $this->ensurePrice($stripe, $product->id, 'xs_pro_web_monthly', 499, 'month');
            $annual = $this->ensurePrice($stripe, $product->id, 'xs_pro_web_annual', 3999, 'year');
        } catch (Throwable $e) {
            $this->error('Stripe API error: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->line('Add these to your .env, then run `php artisan config:clear`:');
        $this->newLine();
        $this->line("STRIPE_PRICE_MONTHLY={$monthly}");
        $this->line("STRIPE_PRICE_ANNUAL={$annual}");
        $this->newLine();
        $this->comment('The 7-day free trial is applied automatically at checkout (config services.stripe.trial_days).');

        return self::SUCCESS;
    }

    private function ensurePrice(StripeClient $stripe, string $productId, string $lookupKey, int $amountCents, string $interval): string
    {
        $existing = $stripe->prices->all(['lookup_keys' => [$lookupKey], 'limit' => 1])->data[0] ?? null;

        if ($existing) {
            $this->info("Reusing existing price {$existing->id} ({$lookupKey})");

            return $existing->id;
        }

        $price = $stripe->prices->create([
            'product' => $productId,
            'unit_amount' => $amountCents,
            'currency' => 'usd',
            'recurring' => ['interval' => $interval],
            'lookup_key' => $lookupKey,
            'nickname' => $lookupKey,
        ]);

        $this->info("Created price {$price->id} ({$lookupKey})");

        return $price->id;
    }
}
