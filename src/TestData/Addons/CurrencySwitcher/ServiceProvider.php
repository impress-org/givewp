<?php

namespace Give\TestData\Addons\CurrencySwitcher;

use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as GiveServiceProvider;

/**
 * Class ServiceProvider
 * @package Give\TestData\CurrencySwitcher
 */
class ServiceProvider implements GiveServiceProvider
{
    /**
     * @inheritDoc
     */
    public function register()
    {
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        // Add donation currency meta
        Hooks::addAction('give-test-data-insert-donation', CurrencySwitcher::class, 'addDonationCurrencyMeta', 10, 2);
        // Set donation currency
        Hooks::addFilter('give-test-data-donation-definition', CurrencySwitcher::class, 'setDonationCurrency', 10, 2);
    }
}
