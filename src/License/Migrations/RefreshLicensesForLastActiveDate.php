<?php

namespace Give\License\Migrations;

use Give\Framework\Migrations\Contracts\Migration;

/**
 * @unreleased
 */
class RefreshLicensesForLastActiveDate extends Migration
{
    /**
     * @unreleased
     */
    public static function id(): string
    {
        return 'refresh-licenses-for-last-active-date';
    }

    /**
     * @unreleased
     */
    public static function title(): string
    {
        return 'Refresh Licenses to set Last Active license date';
    }

    /**
     * @unreleased
     */
    public static function timestamp(): int
    {
        return strtotime('2025-01-15 00:00:00');
    }

    /**
     * This migration refreshes the stored licenses to ensure the grace period logic works
     * correctly for existing installations that had active licenses before this feature was added.
     *
     * @unreleased
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


