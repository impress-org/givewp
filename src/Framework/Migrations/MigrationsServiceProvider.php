<?php

namespace Give\Framework\Migrations;

use Give\Framework\Migrations\Controllers\ManualMigration;
use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider;

/**
 * Class DatabaseServiceProvider
 * @package Give\Framework\Migrations
 *
 * @since 2.9.0
 */
class MigrationsServiceProvider implements ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register()
    {
        give()->singleton(MigrationsRunner::class);
        give()->singleton(MigrationsRegister::class);
    }

    /**
     * @inheritdoc
     */
    public function boot()
    {
        Hooks::addAction('admin_init', ManualMigration::class, '__invoke', 0);
        Hooks::addAction('admin_init', MigrationsRunner::class, 'run', 0);
        //Hooks::addAction('give_upgrades', MigrationsRunner::class, 'run', 0);
        // running batch actions via cron doesn't trigger give_upgrades and all registered actions fail
        Hooks::addAction('action_scheduler_init', MigrationsRunner::class, 'run');
    }
}
