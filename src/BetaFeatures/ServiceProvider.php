<?php

namespace Give\BetaFeatures;

use Give\BetaFeatures\Facades\FeatureFlag;
use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @since 3.6.0
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @since 3.6.0
     * @inheritDoc
     */
    public function register(): void
    {
        //
    }

    /**
     * @since 3.6.0
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
            echo sprintf('<div class="give-admin-beta-features-message"><span class="givewp-beta-icon">BETA</span> %s </div>', __('Beta features are a way to get early access to new features. These features are functional but will be updated frequently. Updates may include changes to the feature settings, admin screens, design and database.', 'give'));
        });

        add_action('give_admin_field_beta_features_feedback_link', function () {
            echo sprintf(
                '<div class="give-admin-beta-features-feedback-link"><p><img src="%s" alt="feedback link icon" /> %s <a href="https://feedback.givewp.com/events-beta-feedback" target="_blank" rel="noopener noreferrer">%s</a></p></div>',
                GIVE_PLUGIN_URL . 'assets/dist/images/admin/feedback-icon.svg',
                __('How can we improve this feature?', 'give'),
                __('Submit your feedback.', 'give')
            );
        });
    }

    /**
     * @since 3.6.0
     */
    private function setFeatureFlagNotificationCounter(): void
    {
        if (get_option('givewp_feature_flag_notifications_count', false) === false) {
            update_option('givewp_feature_flag_notifications_count', 1);
        }
    }

    /**
     * @since 3.6.0
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
