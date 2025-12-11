<?php

namespace Give\License\Migrations;

use Give\Framework\Migrations\Contracts\Migration;

/**
 * @since 4.8.0
 */
class RefreshLicensesForLastActiveDate extends Migration
{
    /**
     * @since 4.8.0
     */
    public static function id(): string
    {
        return 'refresh-licenses-for-last-active-date';
    }

    /**
     * @since 4.8.0
     */
    public static function title(): string
    {
        return 'Refresh Licenses to set Last Active license date';
    }

    /**
     * @since 4.8.0
     */
    public static function timestamp(): int
    {
        return strtotime('2025-09-05 00:00:00');
    }

    /**
     * This migration refreshes the stored licenses to ensure the grace period logic works
     * correctly for existing installations that had active licenses before this feature was added.
     *
     * @since 4.8.0
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


