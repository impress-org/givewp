<?php

namespace Give\Donors\Migrations;

use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;

/**
 * @since 3.7.0
 */
class AddPhoneColumn extends Migration
{
    /**
     * @since 3.7.0
     *
     * @throws DatabaseMigrationException
     */
    public function run()
    {
        global $wpdb;

        $donorsTableName = "{$wpdb->prefix}give_donors";

        try {
            maybe_add_column(
                $donorsTableName,
                'phone',
                "ALTER TABLE `$donorsTableName` ADD COLUMN `phone` varchar(50) NOT NULL DEFAULT '' AFTER `name`"
            );
        } catch (DatabaseQueryException $exception) {
            throw new DatabaseMigrationException('An error occurred adding the phone column to the donors table',
                0, $exception);
        }
    }

    /**
     * @since 3.7.0
     */
    public static function id(): string
    {
        return 'donors-add-phone-column';
    }

    /**
     * @since 3.7.0
     */
    public static function title(): string
    {
        return 'Add phone column to donors table';
    }

    /**
     * @since 3.7.0
     */
    public static function timestamp()
    {
        return strtotime('2024-26-03');
    }
}
