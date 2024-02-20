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
        return give_is_setting_enabled(
            give_get_option("enable_$feature", true)
        );
    }
}
