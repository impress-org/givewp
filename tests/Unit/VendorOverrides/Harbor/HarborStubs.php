<?php

declare(strict_types=1);

namespace Give\Tests\Unit\VendorOverrides\Harbor;

/**
 * Holds per-test return values for the Harbor global function stubs declared
 * in harbor-stub-functions.php (loaded from tests/bootstrap.php). Set the
 * public static properties before exercising code that calls
 * lw_harbor_is_product_license_active() or lw_harbor_is_feature_available(),
 * then call reset() in tearDown.
 *
 * @since 4.15.0
 */
class HarborStubs
{
    /**
     * Controls the return value of lw_harbor_is_product_license_active().
     *
     * @since 4.15.0
     */
    public static bool $productActive = false;

    /**
     * Controls the return value of lw_harbor_is_feature_available().
     * Add slug strings to make the function return true for those slugs.
     *
     * @since 4.15.0
     * @var string[]
     */
    public static array $availableFeatures = [];

    /**
     * Resets all stubs to their default (inactive) state.
     *
     * @since 4.15.0
     */
    public static function reset(): void
    {
        self::$productActive     = false;
        self::$availableFeatures = [];
    }
}
