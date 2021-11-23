<?php

namespace Give\Email;

use Give\Helpers\Hooks;

/**
 * @since 2.17.1
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
    }
}
