<?php

namespace Give\FeatureFlags;

use Give\FeatureFlags\LegacyForms\Settings\Advanced;
use Give\FeatureFlags\LegacyForms\Settings\DefaultOptions;
use Give\FeatureFlags\LegacyForms\Settings\General;
use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider;

/**
 * @unreleased
 */
class FeatureFlagsServiceProvider implements ServiceProvider
{
    /**
     * @unreleased
     */
    public function register()
    {
        // TODO: Implement register() method.
    }

    /**
     * @unreleased
     */
    public function boot()
    {
        // General Tab
        Hooks::addFilter('give_get_sections_general', General::class, 'disableSections');
        Hooks::addFilter('give_get_settings_general', General::class, 'disableOptions');

        // Default Options Tab
        Hooks::addFilter('give_default_setting_tab_section_display', DefaultOptions::class, 'setDefaultTab', 999);
        Hooks::addFilter('give_get_sections_display', DefaultOptions::class, 'disableSections', 999);
        Hooks::addFilter('give_get_settings_display', DefaultOptions::class, 'disableOptions', 999);

        // Advance Tab
        Hooks::addFilter('give_get_sections_advanced', Advanced::class, 'disableSections', 999);
        Hooks::addFilter('give_get_settings_advanced', Advanced::class, 'disableOptions', 999);
    }
}
