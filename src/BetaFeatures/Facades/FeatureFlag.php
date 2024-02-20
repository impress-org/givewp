<?php

namespace Give\BetaFeatures\Facades;

use Give\Framework\Support\Facades\Facade;

/**
 * @method static bool eventTickets()
 * @method static bool enabled(string $feature)
 */
class FeatureFlag extends Facade
{
    protected function getFacadeAccessor()
    {
        return \Give\BetaFeatures\Repositories\FeatureFlagRepository::class;
    }
}
