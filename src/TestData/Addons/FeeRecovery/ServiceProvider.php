<?php

namespace Give\TestData\Addons\FeeRecovery;

use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as GiveServiceProvider;

/**
 * Class ServiceProvider
 * @package GiveTestData\FeeRecovery
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
        Hooks::addAction('give-test-data-insert-donation', FeeRecovery::class, 'addFee', 10, 3);
    }
}
