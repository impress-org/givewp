<?php

namespace Give\MigrationLog\Migrations;

use Give\Framework\Database\DB;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;

/**
 * Class CreateMigrationsTable
 * @package Give\MigrationLog\Migrations
 *
 * @since 2.10.0
 */
class CreateMigrationsTable extends Migration
{
    /**
     * @return string
     */
    public static function id()
    {
        return 'create_migrations_table';
    }

    /**
     * @return string
     */
    public static function title()
    {
        return esc_html__('Create new give_migrations table', 'give');
    }

    /**
     * @return int
     */
    public static function timestamp()
    {
        /**
         * For this migration, we have to use the earliest possible date because we will be using
         * the table created with this migration to store the status of the migration
         */
        return strtotime('1970-01-01 00:00');
    }

    public function run()
    {
        if( $this->isTableExist() ) {
            return;
        }

        $table = $this->getTableName();
        $charset = DB::get_charset_collate();

        $sql = "CREATE TABLE {$table} (
			id VARCHAR(180) NOT NULL,
			status VARCHAR(16) NOT NULL,
			error text NULL,
			last_run DATETIME NOT NULL,
			PRIMARY KEY  (id)
		) {$charset}";

        try {
            DB::delta($sql);
        } catch (DatabaseQueryException $exception) {
            throw new DatabaseMigrationException("An error occurred while creating the {$table} table", 0, $exception);
        }
    }

    /**
     * @return
     * @return bool
     */
    private function isTableExist()
    {
        $result = DB::get_var(
            DB::prepare(
                'SELECT COUNT(*)
            FROM information_schema.tables
            WHERE table_schema = DATABASE()
            AND table_name=%s;',
                $this->getTableName()
            )
        );

        return '1' === $result;
    }

    /**
     * @unreleased
     * @return string
     */
    private function getTableName()
    {
        return DB::prefix('give_migrations');
    }
}
