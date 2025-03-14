<?php

namespace Give\Settings;

use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;
use Give\Settings\Security\Actions\RegisterPage;
use Give\Settings\Security\Actions\RegisterSection;
use Give\Settings\Security\Actions\RegisterSettings;

/**
 * Class ServiceProvider
 *
 * @since 3.17.0
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @since 3.17.0
     */
    public function register()
    {
    }

    /**
     * @since 3.17.0
     */
    public function boot()
    {
        $this->registerSecuritySettings();
    }

    /**
     * @since 3.17.0
     */
    private function registerSecuritySettings(): void
    {
        Hooks::addFilter('give-settings_get_settings_pages', RegisterPage::class);
        Hooks::addFilter('give_get_sections_security', RegisterSection::class);
        Hooks::addFilter('give_get_settings_security', RegisterSettings::class);
    }
}
