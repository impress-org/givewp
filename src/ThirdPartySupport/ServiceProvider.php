<?php

namespace Give\ThirdPartySupport;

use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;
use Give\ThirdPartySupport\Polylang\Helpers\Polylang;
use Give\ThirdPartySupport\WPML\Helpers\WPML;
use Give\ThirdPartySupport\Elementor\Widgets\DonationHistoryWidget;
use Give\ThirdPartySupport\Elementor\Widgets\DonationReceiptWidget;
use Give\ThirdPartySupport\Elementor\Widgets\GiveDonorWallWidget;
use Give\ThirdPartySupport\Elementor\Widgets\GiveFormGridWidget;
use Give\ThirdPartySupport\Elementor\Widgets\GiveFormWidget;
use Give\ThirdPartySupport\Elementor\Widgets\GiveGoalWidget;
use Give\ThirdPartySupport\Elementor\Widgets\GiveLoginWidget;
use Give\ThirdPartySupport\Elementor\Widgets\GiveMultiFormGoalWidget;
use Give\ThirdPartySupport\Elementor\Widgets\GiveProfileEditorWidget;
use Give\ThirdPartySupport\Elementor\Widgets\GiveRegisterWidget;
use Give\ThirdPartySupport\Elementor\Widgets\GiveSubscriptionsWidget;
use Give\ThirdPartySupport\Elementor\Widgets\GiveTotalsWidget;

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
        if (!defined('ELEMENTOR_VERSION')) {
            return;
        }

        // Register core widgets with priority 11 to override any widgets from previously migrated plugin givewp-elementor-widgets
        add_action('elementor/widgets/register', function ($widgets_manager) {
            // Register new core widgets
            /** @var \Elementor\Widgets_Manager $widgets_manager */
            $widgets_manager->register(new DonationHistoryWidget());
            $widgets_manager->register(new DonationReceiptWidget());
            $widgets_manager->register(new GiveDonorWallWidget());
            $widgets_manager->register(new GiveFormGridWidget());
            $widgets_manager->register(new GiveFormWidget());
            $widgets_manager->register(new GiveGoalWidget());
            $widgets_manager->register(new GiveLoginWidget());
            $widgets_manager->register(new GiveMultiFormGoalWidget());
            $widgets_manager->register(new GiveProfileEditorWidget());
            $widgets_manager->register(new GiveRegisterWidget());
            $widgets_manager->register(new GiveTotalsWidget());

            if (defined('GIVE_RECURRING_VERSION')) {
                $widgets_manager->register(new GiveSubscriptionsWidget());
            }
        }, 11, 1);

        $this->deactivateGivewpElementorWidgets();

        add_action('elementor/editor/before_enqueue_scripts', function () {
            wp_enqueue_style('give-elementor-admin-styles', GIVE_PLUGIN_URL . 'src/ThirdPartySupport/Elementor/Widgets/resources/styles/give-elementor-admin.css', array(), GIVE_VERSION);
        });
    }

    /**
     * Deactivate the GiveWP Elementor Widgets plugin if it is installed and activated
     *
     * @since @unreleased
     */
    private function deactivateGivewpElementorWidgets()
    {
        if (!defined('GiveWP_DW_4_Elementor_VERSION')) {
            return;
        }

        if (!is_plugin_active('givewp-elementor-widgets/givewp-elementor-widgets.php')) {
            return;
        }

        deactivate_plugins(['givewp-elementor-widgets/givewp-elementor-widgets.php']);

        add_action('admin_notices', function () {
            Give()->notices->register_notice([
                'id' => 'givewp-elementor-widgets-plugin-deactivated',
                'description' => __(
                    'The GiveWP Elementor Widgets plugin is no longer needed, since the widgets are included in your current version of GiveWP. To prevent conflicts, the plugin has been deactivated and can be safely deleted.',
                    'give'
                ),
                'type' => 'info',
            ]);
        });
    }
}
