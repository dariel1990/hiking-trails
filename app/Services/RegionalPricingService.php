<?php

namespace App\Services;

class RegionalPricingService
{
    /**
     * Specific price points per country.
     * currency: ISO 4217 code passed to Stripe checkout (requires matching Stripe Price or Adaptive Pricing).
     * symbol:   Display symbol shown in the UI.
     * monthly / annual: Display amounts (string to preserve formatting like "1,950").
     */
    private const PRICING = [
        'CA' => ['currency' => 'CAD', 'symbol' => '$',  'monthly' => '4.99',  'annual' => '39.99'],
        'US' => ['currency' => 'USD', 'symbol' => '$',  'monthly' => '3.99',  'annual' => '31.99'],
        'PH' => ['currency' => 'PHP', 'symbol' => '₱',  'monthly' => '240',   'annual' => '1,950'],
        'GB' => ['currency' => 'GBP', 'symbol' => '£',  'monthly' => '2.99',  'annual' => '24.99'],
        'AU' => ['currency' => 'AUD', 'symbol' => '$',  'monthly' => '5.99',  'annual' => '47.99'],
    ];

    private const DEFAULT_COUNTRY = 'CA';

    public function __construct(private GeoIpService $geo) {}

    /**
     * Return pricing for the current request's IP, falling back to CAD defaults.
     *
     * @return array{currency: string, symbol: string, monthly: string, annual: string, country: string}
     */
    public function forRequest(): array
    {
        $ip = request()->ip() ?? '';
        $country = $this->geo->countryCode($ip);

        return array_merge($this->forCountry($country), ['country' => $country]);
    }

    /**
     * Pricing entry for a country code, defensively falling back to the
     * hardcoded table when the admin-edited setting is missing or malformed.
     *
     * @return array{currency: string, symbol: string, monthly: string, annual: string}
     */
    public function forCountry(string $country): array
    {
        $pricing = $this->pricingTable();
        $default = $pricing[$this->defaultCountry()] ?? self::PRICING[self::DEFAULT_COUNTRY];
        $entry = $pricing[$country] ?? $default;

        foreach (['currency', 'symbol', 'monthly', 'annual'] as $field) {
            if (empty($entry[$field]) || ! is_string($entry[$field])) {
                return $default;
            }
        }

        return $entry;
    }

    /**
     * @return array<string, array{currency: string, symbol: string, monthly: string, annual: string}>
     */
    public function pricingTable(): array
    {
        $pricing = setting('regional_pricing');

        return is_array($pricing) && $pricing !== [] ? $pricing : self::PRICING;
    }

    public function defaultCountry(): string
    {
        return setting('default_country', self::DEFAULT_COUNTRY);
    }
}
