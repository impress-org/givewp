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

    private function elementor()
    {
        if ( ! class_exists( '\Elementor\Plugin' ) ) {
            return;
        }

        // Register core widgets with priority 11 to override any widgets from previously migrated plugin givewp-elementor-widgets
        add_action('elementor/widgets/register', function (\Elementor\Widgets_Manager $widgets_manager) {
            // Register new core widgets
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
    }

    /**
     * TODO: check if this is needed.
     * Unregister old widgets from givewp-elementor-widgets plugin to prevent conflicts
     *
     * @since @unreleased
     */
    private function unregisterOldElementorWidgets(\Elementor\Widgets_Manager $widgets_manager)
    {
        // Widget names from the old givewp-elementor-widgets plugin
        $old_widget_names = [
            'Donation History',
            'GiveWP Donation Receipt',
            'GiveWP Donor Wall',
            'GiveWP Form Grid',
            'GiveWP Donation Form',
            'GiveWP Goal',
            'GiveWP Login',
            'GiveWP Multi Form Goal Block',
            'GiveWP Profile Editor',
            'GiveWP Register',
            'GiveWP Totals',
            'GiveWP Subscriptions',
        ];

        foreach ($old_widget_names as $widget_name) {
             try {
                 $widgets_manager->unregister($widget_name);
             } catch (\Exception $e) {
                 // Widget doesn't exist, continue silently
             }
         }
    }
}
