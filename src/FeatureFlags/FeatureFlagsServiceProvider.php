<?php

namespace Give\FeatureFlags;

use Give\FeatureFlags\OptionBasedFormEditor\OptionBasedFormEditor;
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
    }

    /**
     * @unreleased
     */
    public function boot()
    {
        $this->maybeDisableOptionBasedFormEditorSettings();
    }

    /**
     * @return void
     */
    private function maybeDisableOptionBasedFormEditorSettings()
    {
        // General Tab
        Hooks::addFilter('give_get_sections_general', General::class, 'maybeDisableSections', 999);
        Hooks::addFilter('give_get_settings_general', General::class, 'maybeDisableOptions', 999);

        // Payment Gateways Tab
        add_filter('give_settings_payment_gateways_menu_groups', function ($groups) {
            if ( ! OptionBasedFormEditor::isEnabled() && isset($groups['v2'])) {
                unset($groups['v2']);
            }

            return $groups;
        });

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
