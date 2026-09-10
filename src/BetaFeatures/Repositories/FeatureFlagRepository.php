<?php

namespace Give\BetaFeatures\Repositories;

use Give\Framework\Permissions\Facades\UserPermissions;

class FeatureFlagRepository
{
    /**
     * @since 3.6.0
     */
    public function eventTickets(): bool
    {
        if (defined('GIVE_FEATURE_ENABLE_EVENT_TICKETS')){
            return GIVE_FEATURE_ENABLE_EVENT_TICKETS === true;
        }

        return $this->enabled('event_tickets', false);
    }

    /**
     * In the future this will be dynamic, however right now we need a simple iteration of a notifications counter.
     *
     * @since 4.13.1 added filter givewp_feature_flag_notifications_count
     * @since 3.6.0
     */
    public function getNotificationCount(): int
    {
        $count = (int)get_option('givewp_feature_flag_notifications_count', 0);

        return apply_filters('givewp_feature_flag_notifications_count', $count);
    }

    /**
     * @since 3.6.0
     */
    public function resetNotificationCount(): void
    {
        update_option('givewp_feature_flag_notifications_count', 0);
    }

    /**
     * @since 4.16.8.1 Only trust $_POST during a verified settings-save request, not any request.
     * @since 3.6.0
     */
    public function enabled($feature, $default = false): bool
    {
        // Workaround so that the updated option is available at the start of the request.
        $option = ($this->isVerifiedSettingsSaveRequest() && isset($_POST["enable_$feature"]))
            ? give_clean($_POST["enable_$feature"])
            : give_get_option("enable_$feature", $default);

        return give_is_setting_enabled($option);

    }

    /**
     * @since 4.16.8.1
     */
    private function isVerifiedSettingsSaveRequest(): bool
    {
        return UserPermissions::settings()->canManage()
            && class_exists('Give_Admin_Settings')
            && \Give_Admin_Settings::verify_nonce();
    }
}
