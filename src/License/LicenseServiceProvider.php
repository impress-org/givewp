<?php

namespace Give\License;

use Give\Framework\Migrations\MigrationsRegister;
use Give\License\Migrations\RefreshLicensesForPlatformFee;
use Give\License\Repositories\LicenseRepository;
use Give\ServiceProviders\ServiceProvider;

class LicenseServiceProvider implements ServiceProvider
{
    /**
     * @unreleased added LicenseRepository singleton
     * @since 2.11.3
     */
    public function register()
    {
        give()->singleton(PremiumAddonsListManager::class);
        give()->singleton(LicenseRepository::class);
    }

    /**
     * @unreleased added refresh-licenses-for-platform-fee migration
     * @since 2.11.3
     */
    public function boot()
    {
        /** @var MigrationsRegister $migrationRegistrar */
        $migrationRegistrar = give(MigrationsRegister::class);

        $migrationRegistrar->addMigration(RefreshLicensesForPlatformFee::class);
    }
}
