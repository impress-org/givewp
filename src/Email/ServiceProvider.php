<?php

namespace Give\Email;

use Give\Email\Migrations\SetDefaultEmailRecipientToAdminEmail;
use Give\Framework\Migrations\MigrationsRegister;
use Give\Helpers\Hooks;

/**
 * @unreleased
 */
class ServiceProvider implements \Give\ServiceProviders\ServiceProvider
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
        Hooks::addAction('admin_init', GlobalSettingValidator::class);
        give(MigrationsRegister::class)->addMigration(SetDefaultEmailRecipientToAdminEmail::class);
    }
}
