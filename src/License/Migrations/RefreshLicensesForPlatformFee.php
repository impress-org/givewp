<?php

namespace Give\License\Migrations;

use Give\Framework\Migrations\Contracts\Migration;

/**
 * @unreleased
 */
class RefreshLicensesForPlatformFee extends Migration
{
    /**
     * @unreleased
     */
    public static function id(): string
    {
        return 'refresh-licenses-for-platform-fee';
    }

    /**
     * @unreleased
     */
    public static function title(): string
    {
        return 'Refresh Licenses for Platform Fee';
    }

    /**
     * @unreleased
     */
    public static function timestamp(): int
    {
        return strtotime('2025-05-01 00:00:00');
    }

    /**
     * @unreleased
     */
    public function run(): void
    {
        if (function_exists('give_refresh_licenses')) {
            give_refresh_licenses();
        }
    }
}
