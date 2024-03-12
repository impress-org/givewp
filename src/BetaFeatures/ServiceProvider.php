<?php

namespace Give\BetaFeatures;

use Give\BetaFeatures\Facades\FeatureFlag;
use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @unreleased
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @unreleased
     * @inheritDoc
     */
    public function register(): void
    {
        //
    }

    /**
     * @unreleased
     * @inheritDoc
     */
    public function boot(): void
    {
        $this->setFeatureFlagNotificationCounter();

        Hooks::addFilter('give_get_settings_general', Actions\RegisterSettings::class);
        Hooks::addFilter('give_get_sections_general', Actions\RegisterSettingSection::class);

        add_filter('givewp_settings_menu_title_give-settings', function ($title) {
            return $this->getTitleWithNotificationCounter($title, 'menu-counter');
        });

        add_filter('givewp_settings_section_title_beta', function ($title) {
            return $this->getTitleWithNotificationCounter($title);
        });

        add_action('give_admin_field_beta_features', function(){
            echo sprintf('<div class="give-admin-beta-features-message"><p><span class="givewp-beta-icon">BETA</span> %s</p></div>', __('Beta features are a way to test out upcoming features.  They are nearly complete, yet still under development and may require changes in the future.'));
        });
    }

    /**
     * @unreleased
     */
    private function setFeatureFlagNotificationCounter(): void
    {
        if (get_option('givewp_feature_flag_notifications_count', false) === false) {
            update_option('givewp_feature_flag_notifications_count', 1);
        }
    }

    /**
     * @unreleased
     */
    public function getTitleWithNotificationCounter($title, $className = ''): string
    {
        $count = FeatureFlag::getNotificationCount();

        if (!$count) {
            return $title;
        }

        $counter = sprintf(
            ' <span class="%s givewp-feature-flag-notification-counter count-%s"><span class="count">%s</span></span>',
            $className,
            $count,
            number_format_i18n($count)
        );

        return $title . $counter;
    }
}
