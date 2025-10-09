<?php

namespace Give\Donors\Migrations;

use Give\Donors\ValueObjects\DonorStatus;
use Give\Framework\Database\DB;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;

/**
 * @unreleased
 */
class AddStatusColumn extends Migration
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'add_status_column_to_donors_table';
    }

    /**
     * @inheritDoc
     */
    public static function title(): string
    {
        return 'Add status column to the give_donors table';
    }

    /**
     * @inheritdoc
     */
    public static function timestamp(): string
    {
        return strtotime('2025-10-07 00:00:00');
    }

    /**
     * @inheritDoc
     * @throws DatabaseMigrationException
     */
    public function run()
    {
        $table = DB::prefix('give_donors');
        $defaultStatus = DonorStatus::ACTIVE;

        $columnAdded = maybe_add_column($table, 'status', "ALTER TABLE $table ADD COLUMN status VARCHAR(12) NOT NULL DEFAULT '$defaultStatus'");

        if ( ! $columnAdded) {
            throw new DatabaseMigrationException("An error occurred while updating the $table table");
        }
    }
}
