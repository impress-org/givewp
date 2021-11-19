<?php

namespace Give\TestData\Addons\ManualDonations;

use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as GiveServiceProvider;

/**
 * Class ServiceProvider
 * @package Give\TestData\ManualDonations
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
        // Update donation meta on donation insert
        Hooks::addAction('give-test-data-insert-donation', ManualDonations::class, 'updateDonationMeta', 10, 2);
    }
}
