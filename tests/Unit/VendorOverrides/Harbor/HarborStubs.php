<?php

declare(strict_types=1);

namespace Give\Tests\Unit\VendorOverrides\Harbor;

/**
 * Holds per-test return values for the Harbor global function stubs registered in
 * tests/bootstrap.php. Set the public static properties before exercising code that
 * calls lw_harbor_is_product_license_active() or
 * lw_harbor_is_feature_available(), then call reset() in tearDown.
 *
 * @unreleased
 */
class HarborStubs
{
    /**
     * Controls the return value of lw_harbor_is_product_license_active().
     *
     * @unreleased
     */
    public static bool $productActive = false;

    /**
     * Controls the return value of lw_harbor_is_feature_available().
     * Add slug strings to make the function return true for those slugs.
     *
     * @unreleased
     * @var string[]
     */
    public static array $availableFeatures = [];

    /**
     * Resets all stubs to their default (inactive) state.
     *
     * @unreleased
     */
    public static function reset(): void
    {
        self::$productActive      = false;
        self::$availableFeatures  = [];
    }
}
