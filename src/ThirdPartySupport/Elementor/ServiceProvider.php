<?php

namespace Give\ThirdPartySupport\Elementor;

use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;
use Give\ThirdPartySupport\Elementor\Actions\RegisterWidgets;
use Give\ThirdPartySupport\Elementor\Actions\RegisterWidgetEditorScripts;
use Give\ThirdPartySupport\Elementor\Actions\SetupElementorCampaignTemplate;
use Give\ThirdPartySupport\Elementor\Actions\UnregisterV1Widgets;
use Give\ThirdPartySupport\Elementor\Settings\RegisterSection;
use Give\ThirdPartySupport\Elementor\Settings\RegisterSettings;

/**
 * @since 4.7.0
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @since 4.7.0
     */
    public function register() {}

    /**
     * @since 4.7.0
     */
    public function boot()
    {
        $this->registerElementorSettings();

        if (!defined('ELEMENTOR_VERSION')) {
            return;
        }

        // If the old version of the GiveWP Elementor Widgets plugin is installed, unregister the legacy widgets to prevent conflicts with the new widgets that are registered in the RegisterWidgets class
        $this->maybeUnregisterExistingLegacyWidgets();

        // Register core widgets with priority 11 to override any widgets from previously migrated plugin givewp-elementor-widgets
        Hooks::addFilter('elementor/widgets/register', RegisterWidgets::class, '__invoke', 11, 1);

        // Register widget scripts for the editor preview
        Hooks::addAction('elementor/preview/enqueue_scripts', RegisterWidgetEditorScripts::class);

        // Register admin styles
        add_action('elementor/editor/before_enqueue_scripts', function () {
            wp_enqueue_style('give-elementor-admin-styles', GIVE_PLUGIN_URL . 'src/ThirdPartySupport/Elementor/Widgets/resources/styles/give-elementor-admin.css', array(), GIVE_VERSION);
        });

        // Register elementor categories
        add_action('elementor/elements/categories_registered', function ($elements_manager) {
            /** @var \Elementor\Elements_Manager $elements_manager */
            $elements_manager->add_category(
                'givewp-category-legacy',
                [
                    'title' => __('GiveWP (Legacy)', 'give'),
                    'icon' => 'dashicons dashicons-give',
                ]
            );

            $elements_manager->add_category(
                'givewp-category',
                [
                    'title' => __('GiveWP', 'give'),
                    'icon' => 'dashicons dashicons-give',
                ]
            );
        });

        Hooks::addAction('givewp_campaign_page_created', SetupElementorCampaignTemplate::class);
    }

    /**
     * Register the GiveWP Elementor Widgets settings
     *
     * @since @since 4.7.0
     */
    public function registerElementorSettings()
    {
        Hooks::addFilter('give_get_sections_display', RegisterSection::class);
        Hooks::addFilter('give_get_settings_display', RegisterSettings::class);
    }

    /**
     * If the old version of the GiveWP Elementor Widgets plugin is installed, unregister the legacy widgets to prevent conflicts with the new widgets that are registered in the RegisterWidgets class
     *
     * @since @since 4.7.0
     */
    private function maybeUnregisterExistingLegacyWidgets()
    {
        // This would determine if the old version of the GiveWP Elementor Widgets plugin is installed
        if (!defined('GiveWP_DW_4_Elementor_VERSION')) {
            return;
        }

        // If the option is not set, set it to disabled
        if (give_get_option('givewp_elementor_legacy_widgets_enabled') === false) {
            // update the option initially to enable the legacy widgets
            // users can disable the legacy widgets in the GiveWP Elementor Widgets settings from here on out
            give_update_option('givewp_elementor_legacy_widgets_enabled', 'enabled');
        }

        // Unregister the legacy widgets from the GiveWP Elementor Widgets plugin to prevent conflicts with the new widgets that are registered in the RegisterWidgets class
        Hooks::addFilter('elementor/widgets/register', UnregisterV1Widgets::class, '__invoke', 11, 1);

        add_action('admin_notices', function () {
            // Define notice configuration
            $notice_config = [
                'id' => 'givewp-elementor-widgets-plugin-legacy-plugin-notice',
                'description' => __(
                    'GiveWP now includes Elementor widgets! You can safely deactivate and remove the GiveWP Elementor Widgets plugin as it is no longer needed. Rest assured, all of your widgets currently being used will remain working properly.',
                    'give'
                ),
                'type' => 'info',
                'dismissible_type' => 'user',
                'dismiss_interval' => 'permanent',
            ];

            // Only register the notice if it hasn't been dismissed
            if (!Give()->notices->is_notice_dismissed($notice_config)) {
                Give()->notices->register_notice($notice_config);
            }
        });
    }
}
