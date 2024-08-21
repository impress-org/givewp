<?php

namespace Give\Campaigns;

use Give\Campaigns\Migrations\MigrateFormsToCampaignForms;
use Give\Framework\Migrations\MigrationsRegister;
use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @unreleased
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @unreleased
     * @inheritDoc
     */
    public function register(): void
    {
        //
    }

    /**
     * @unreleased
     * @inheritDoc
     */
    public function boot(): void
    {
        give(MigrationsRegister::class)->addMigrations([
            MigrateFormsToCampaignForms::class,
        ]);
    }
}
