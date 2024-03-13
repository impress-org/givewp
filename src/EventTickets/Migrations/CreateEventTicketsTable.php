<?php

namespace Give\EventTickets\Migrations;

use Give\Framework\Database\DB;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;

/**
 * @since 3.6.0
 */
class CreateEventTicketsTable extends Migration {
    /**
     * @inheritdoc
     */
    public static function id() {
        return 'give-events-create-events-tickets-table';
    }

    public static function title() {
        return 'Create give_event_tickets table';
    }

    /**
     * @inheritdoc
     */
    public static function timestamp() {
        return strtotime( '2022-01-29 02:00:00' );
    }

    /**
     * @inheritdoc
     * @throws DatabaseMigrationException
     */
    public function run() {
        global $wpdb;

        $table   = $wpdb->give_event_tickets;
        $charset = DB::get_charset_collate();

        // id BINARY(16) DEFAULT (UUID_TO_BIN(UUID())),
        $sql = "CREATE TABLE $table (
			id INT UNSIGNED NOT NULL AUTO_INCREMENT,
			event_id INT UNSIGNED NOT NULL,
			ticket_type_id INT UNSIGNED NOT NULL,
			donation_id INT UNSIGNED NOT NULL,
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
};
