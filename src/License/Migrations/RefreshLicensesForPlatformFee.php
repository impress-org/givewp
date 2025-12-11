<?php

namespace Give\License\Migrations;

use Give\Framework\Migrations\Contracts\Migration;

/**
 * @since 4.3.0
 */
class RefreshLicensesForPlatformFee extends Migration
{
    /**
     * @since 4.3.0
     */
    public static function id(): string
    {
        return 'refresh-licenses-for-platform-fee';
    }

    /**
     * @since 4.3.0
     */
    public static function title(): string
    {
        return 'Refresh Licenses for Platform Fee';
    }

    /**
     * @since 4.3.0
     */
    public static function timestamp(): int
    {
        return strtotime('2025-05-01 00:00:00');
    }

    /**
     * This migration refreshes the stored licenses (making a request to the License Server API) to retrieve the new gateway_fee property.
     *
     * @since 4.3.0
     */
    public function run(): void
    {
        // Normally we avoid using production code within migrations, but this is a simple license refresh that will eventually be refreshed anyway.
        // We check if the function exists now to avoid errors in case in the future, this function is not defined anymore.
        if (function_exists('give_refresh_licenses')) {
            give_refresh_licenses();
        }
    }
}
