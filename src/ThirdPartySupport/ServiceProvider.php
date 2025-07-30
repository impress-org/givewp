<?php

namespace Give\ThirdPartySupport;

use Give\Framework\EnqueueScript;
use Give\Framework\Support\Facades\Scripts\ScriptAsset;
use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;
use Give\ThirdPartySupport\Elementor\Actions\RegisterWidgets;
use Give\ThirdPartySupport\Elementor\Actions\UnregisterV1Widgets;
use Give\ThirdPartySupport\Elementor\Settings\RegisterSection;
use Give\ThirdPartySupport\Elementor\Settings\RegisterSettings;
use Give\ThirdPartySupport\Polylang\Helpers\Polylang;
use Give\ThirdPartySupport\WPML\Helpers\WPML;

/**
 * @since 3.22.0
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @since 3.22.0
     */
    public function register()
    {
    }

    /**
     * @unreleased added support for Elementor
     * @since 3.22.0
     */
    public function boot()
    {
        /**
         * When in the admin area and WPML or Polylang is installed, retrieve the language
         * selected in the language selector of the WordPress admin bar added by them
         */
        add_filter('givewp_locale', function ($locale) {
            if ( ! is_admin()) {
                return $locale;
            }

            $wpmlLocale = WPML::getLocale();
            if ($wpmlLocale != $locale) {
                return $wpmlLocale;
            }

            $polylangLocale = Polylang::getLocale();
            if ($polylangLocale != $locale) {
                return $polylangLocale;
            }

            return $locale;
        });


        $this->elementor();
    }

    /**
     * Register core widgets with priority 11 to override any widgets from previously migrated plugin givewp-elementor-widgets
     *
     * @since @unreleased
     */
    private function elementor()
    {
        $this->registerElementorSettings();

        if (!defined('ELEMENTOR_VERSION')) {
            return;
        }

        // Deactivate the GiveWP Elementor Widgets plugin if it is installed and activated
        $this->maybeDeactivateGivewpElementorWidgetsAddon();

        Hooks::addFilter('elementor/widgets/register', RegisterWidgets::class, '__invoke');

        add_action('elementor/editor/before_enqueue_scripts', function () {
            wp_enqueue_style('give-elementor-admin-styles', GIVE_PLUGIN_URL . 'src/ThirdPartySupport/Elementor/Widgets/resources/styles/give-elementor-admin.css', array(), GIVE_VERSION);
        });

        add_action('wp_enqueue_scripts', function () {
            $scriptAsset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/elementorCampaignFormWidget.asset.php');
            $scriptName = 'givewp-elementor-campaign-form-widget';

            wp_enqueue_style(
                $scriptName,
                GIVE_PLUGIN_URL . 'build/elementorCampaignFormWidget.css',
            );

            wp_register_script(
                $scriptName,
                GIVE_PLUGIN_URL . 'build/elementorCampaignFormWidget.js',
                array_merge($scriptAsset['dependencies'], ['elementor-frontend']),
                $scriptAsset['version'],
                true
            );
        });

        add_action('elementor/elements/categories_registered', function($elements_manager) {
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
    }

    /**
     * Register the GiveWP Elementor Widgets settings
     *
     * @since @unreleased
     */
    public function registerElementorSettings()
    {
        Hooks::addFilter('give_get_sections_display', RegisterSection::class);
        Hooks::addFilter('give_get_settings_display', RegisterSettings::class);
    }

    /**
     * Deactivate the GiveWP Elementor Widgets plugin if it is installed and activated
     *
     * @since @unreleased
     */
    private function maybeDeactivateGivewpElementorWidgetsAddon()
    {
        // This would determine if the old version of the GiveWP Elementor Widgets plugin is installed
        if (!defined('GiveWP_DW_4_Elementor_VERSION')) {
            return;
        }

        // This would determine if the old version of the GiveWP Elementor Widgets plugin is activated
        if (!is_plugin_active('givewp-elementor-widgets/givewp-elementor-widgets.php')) {
            return;
        }

        // update the option to enable the legacy widgets
        give_update_option('givewp_elementor_legacy_widgets_enabled', 'enabled');

        // Unregister the legacy widgets from the GiveWP Elementor Widgets plugin to prevent conflicts with the new widgets that are registered in the RegisterWidgets class
        Hooks::addFilter('elementor/widgets/register', UnregisterV1Widgets::class, '__invoke', 10, 1);

        deactivate_plugins(['givewp-elementor-widgets/givewp-elementor-widgets.php']);

        add_action('admin_notices', function () {
            Give()->notices->register_notice([
                'id' => 'givewp-elementor-widgets-plugin-deactivated',
                'description' => __(
                    'The GiveWP Elementor Widgets plugin is no longer needed, since the widgets are included in your current version of GiveWP.  Rest assured, all of your widgets currently being used will remain working properly.  To prevent potential conflicts, the plugin has been deactivated and can be safely deleted.',
                    'give'
                ),
                'type' => 'info',
            ]);
        });
    }
}
