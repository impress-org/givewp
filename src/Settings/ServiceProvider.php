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
        Hooks::addFilter('give-settings_get_settings_pages', RegisterPage::class);
        Hooks::addFilter('give_get_sections_security', RegisterSection::class);
        Hooks::addFilter('give_get_settings_security', RegisterSettings::class);
    }
}
