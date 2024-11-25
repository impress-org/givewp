<?php

namespace Give\FeatureFlags;

/**
 * @since 3.18.0
 */
interface FeatureFlags
{
    /**
     * @since 3.18.0
     */
    public static function isEnabled(): bool;
}
