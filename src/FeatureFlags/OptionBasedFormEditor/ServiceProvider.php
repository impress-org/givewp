<?php

namespace Give\FeatureFlags\OptionBasedFormEditor;

use Give\FeatureFlags\OptionBasedFormEditor\Settings\Advanced as AdvancedSettings;
use Give\FeatureFlags\OptionBasedFormEditor\Settings\DefaultOptions as DefaultOptionsSettings;
use Give\FeatureFlags\OptionBasedFormEditor\Settings\General as GeneralSettings;
use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @since 3.18.0
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @since 3.18.0
     */
    public function register()
    {
    }

    /**
     * @since 3.18.0
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
        Hooks::addFilter('give_get_settings_general', GeneralSettings::class, 'maybeDisableOptions', 999);

        // Payment Gateways Tab
        add_filter('give_settings_payment_gateways_menu_groups', function ($groups) {
            if ( ! OptionBasedFormEditor::isEnabled() && isset($groups['v2'])) {
                unset($groups['v2']);
            }

            return $groups;
        });

        // Default Options Tab
        Hooks::addFilter('give_get_settings_display', DefaultOptionsSettings::class, 'maybeDisableOptions', 999);

        // Advance Tab
        Hooks::addFilter('give_get_settings_advanced', AdvancedSettings::class, 'maybeDisableOptions', 999);
    }
}
