<?php

declare(strict_types=1);

use Give\Tests\Unit\VendorOverrides\Harbor\HarborStubs;

/**
 * Global Harbor function stubs for tests. Required eagerly from
 * tests/bootstrap.php so these declarations win Harbor's function_exists guard
 * in vendor-prefixed/stellarwp/harbor/src/Harbor/global-functions.php.
 *
 * Per-test return values are controlled via HarborStubs.
 */

function lw_harbor_is_product_license_active(string $product): bool
{
    return HarborStubs::$productActive;
}

function lw_harbor_is_feature_available(string $slug): bool
{
    return in_array($slug, HarborStubs::$availableFeatures, true);
}
