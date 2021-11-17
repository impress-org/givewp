<?php

namespace Give\Email;

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
        Hooks::addAction( 'admin_init', GlobalSettingValidator::class );
    }
}
