<?php


namespace Give\ThirdPartyCompatibility;

use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as CoreServiceProvider;
use Give\ThirdPartyCompatibility\Divi\DeregisterEntityScripts;

/**
 * @since 4.5.0
 */
class ServiceProvider implements CoreServiceProvider
{
    public function register()
    {
        Hooks::addAction('wp_print_scripts', DeregisterEntityScripts::class);
    }

    public function boot()
    {
    }
}
