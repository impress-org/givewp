<?php

namespace Give\EventTickets\Migrations;

use Give\Framework\Database\DB;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;

/**
 * @since 3.6.0
 */
class CreateEventsTable extends Migration {
    /**
     * @inheritdoc
     */
    public static function id() {
        return 'give-events-create-events-table';
    }

    public static function title() {
        return 'Create give_events table';
    }

    /**
     * @inheritdoc
     */
    public static function timestamp() {
        return strtotime( '2022-01-29 00:00:00' );
    }

    /**
     * @inheritdoc
     * @throws DatabaseMigrationException
     */
    public function run() {
        global $wpdb;

        $table   = $wpdb->give_events;
        $charset = DB::get_charset_collate();

        $sql = "CREATE TABLE $table (
			id INT UNSIGNED NOT NULL AUTO_INCREMENT,
			title TEXT NULL,
			description TEXT NULL,
			start_datetime DATETIME NULL,
			end_datetime DATETIME NULL,
			ticket_close_datetime DATETIME NULL,
            created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY  (id)
		) $charset";

        try {
            DB::delta( $sql );
        } catch ( DatabaseQueryException $exception ) {
            throw new DatabaseMigrationException( "An error occurred while creating the $table table", 0, $exception );
        }
    }
}
