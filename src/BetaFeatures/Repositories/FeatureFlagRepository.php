<?php

namespace Give\BetaFeatures\Repositories;

class FeatureFlagRepository
{
    /**
     * @unreleased
     */
    public function eventTickets(): bool
    {
        return $this->enabled('event_tickets', false);
    }

    /**
     * In the future this will be dynamic, however right now we need a simple iteration of a notifications counter.
     *
     * @unreleased
     */
    public function getNotificationCount(): int
    {
        return (int)get_option('givewp_feature_flag_notifications_count', 0);
    }

    /**
     * @unreleased
     */
    public function resetNotificationCount(): void
    {
        update_option('givewp_feature_flag_notifications_count', 0);
    }

    /**
     * @unreleased
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
