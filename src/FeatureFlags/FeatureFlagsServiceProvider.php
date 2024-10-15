<?php

namespace Give\FeatureFlags;

use Give\FeatureFlags\OptionBasedFormEditor\Settings\Advanced;
use Give\FeatureFlags\OptionBasedFormEditor\Settings\DefaultOptions;
use Give\FeatureFlags\OptionBasedFormEditor\Settings\General;
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
        $this->optionBasedFormEditor();
    }

    /**
     * @return void
     */
    private function optionBasedFormEditor()
    {
        // General Tab
        Hooks::addFilter('give_get_sections_general', General::class, 'maybeDisableSections', 999);
        Hooks::addFilter('give_get_settings_general', General::class, 'maybeDisableOptions', 999);

        // Default Options Tab
        Hooks::addFilter('give_default_setting_tab_section_display', DefaultOptions::class, 'maybeSetNewDefaultSection',
            999);
        Hooks::addFilter('give_get_sections_display', DefaultOptions::class, 'maybeDisableSections', 999);
        Hooks::addFilter('give_get_settings_display', DefaultOptions::class, 'maybeDisableOptions', 999);

        // Advance Tab
        Hooks::addFilter('give_get_sections_advanced', Advanced::class, 'maybeDisableSections', 999);
        Hooks::addFilter('give_get_settings_advanced', Advanced::class, 'maybeDisableOptions', 999);
    }
}
