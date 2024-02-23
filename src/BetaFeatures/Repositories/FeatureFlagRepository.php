<?php

namespace Give\BetaFeatures\Repositories;

class FeatureFlagRepository
{
    public function eventTickets(): bool
    {
        return $this->enabled('event_tickets');
    }

    public function enabled($feature): bool
    {
        // Workaround so that the updated option is available at the start of the request.
        $option = isset($_POST["enable_$feature"])
            ? give_clean($_POST["enable_$feature"])
            : give_get_option("enable_$feature", true);

        return give_is_setting_enabled($option);

    }
}
