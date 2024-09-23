<?php

namespace Give\Settings;

use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;
use Give\Settings\Security\Actions\RegisterSection;
use Give\Settings\Security\Actions\RegisterSettings;

/**
 * Class ServiceProvider
 *
 * @unreleased
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @unreleased
     */
    public function register()
    {
    }

    /**
     * @unreleased
     */
    public function boot()
    {
        $this->registerSecuritySettings();
    }

    /**
     * @unreleased
     */
    private function registerSecuritySettings(): void
    {
        Hooks::addFilter('give_get_sections_general', RegisterSection::class);
        Hooks::addFilter('give_get_settings_general', RegisterSettings::class);
    }
}
