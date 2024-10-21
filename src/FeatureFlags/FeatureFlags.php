<?php

namespace Give\FeatureFlags;

/**
 * @unreleased
 */
interface FeatureFlags
{
    public static function isEnabled(): bool;
}
