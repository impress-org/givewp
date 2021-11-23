<?php

namespace Give\Revenue\Migrations;

use Give\Framework\Database\DB;
use Give\Framework\Migrations\Contracts\Migration;

/**
 * Class RemoveRevenueForeignKeys
 *
 * @package Give\Revenue\Migrations
 * @since 2.9.6
 */
class RemoveRevenueForeignKeys extends Migration
{
    /**
     * @inheritDoc
     */
    public static function id()
    {
        return 'remove_revenue_foreign_keys';
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        global $wpdb;

        $this->dropColumnForeignKeyConstraint($wpdb->give_revenue, 'form_id');
        $this->dropColumnForeignKeyConstraint($wpdb->give_revenue, 'donation_id');
    }

    /**
     * @inheritDoc
     */
    public static function timestamp()
    {
        return strtotime('01-05-2020 12:45:00');
    }

    /**
     * Drops the foreign key constraint for a given table and column
     *
     * @since 2.9.6
     *
     * @param string $table
     * @param string $column
     */
    private function dropColumnForeignKeyConstraint($table, $column)
    {
        $constraintName = DB::get_var(
            DB::prepare(
                "
					SELECT constraints.CONSTRAINT_NAME
					FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS as constraints
					JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE as column_usage
						ON constraints.CONSTRAINT_NAME = column_usage.CONSTRAINT_NAME
					WHERE constraints.CONSTRAINT_TYPE = 'FOREIGN KEY'
						AND constraints.TABLE_NAME = %s
						AND column_usage.COLUMN_NAME = %s
				",
                $table,
                $column
            )
        );

        if ( ! empty($constraintName)) {
            // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            DB::query("ALTER TABLE {$table} DROP FOREIGN KEY {$constraintName}");
        }
    }
}
