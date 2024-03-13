<?php

namespace Give\BetaFeatures\Repositories;

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
     * @since 3.6.0
     */
    public function getNotificationCount(): int
    {
        return (int)get_option('givewp_feature_flag_notifications_count', 0);
    }

    /**
     * @since 3.6.0
     */
    public function resetNotificationCount(): void
    {
        update_option('givewp_feature_flag_notifications_count', 0);
    }

    /**
     * @since 3.6.0
     */
    public function enabled($feature, $default = false): bool
    {
        // Workaround so that the updated option is available at the start of the request.
        $option = isset($_POST["enable_$feature"])
            ? give_clean($_POST["enable_$feature"])
            : give_get_option("enable_$feature", $default);

        return give_is_setting_enabled($option);

    }
}
