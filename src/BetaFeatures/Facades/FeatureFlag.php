<?php

namespace Give\BetaFeatures\Facades;

use Give\BetaFeatures\Repositories\FeatureFlagRepository;
use Give\Framework\Support\Facades\Facade;

/**
 * @since 3.6.0
 *
 * @method static void resetNotificationCount()
 * @method static int getNotificationCount()
 * @method static bool eventTickets()
 * @method static bool enabled(string $feature)
 */
class FeatureFlag extends Facade
{
    /**
     * @since 3.6.0
     */
    protected function getFacadeAccessor(): string
    {
        return FeatureFlagRepository::class;
    }
}
