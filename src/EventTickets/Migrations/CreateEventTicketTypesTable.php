<?php

namespace Give\EventTickets\Migrations;

use Give\Framework\Database\DB;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;

/**
 * @unreleased
 */
class CreateEventTicketTypesTable extends Migration {
    /**
     * @inheritdoc
     */
    public static function id() {
        return 'give-events-create-events-ticket-types-table';
    }

    public static function title() {
        return 'Create give_event_ticket_types table';
    }

    /**
     * @inheritdoc
     */
    public static function timestamp() {
        return strtotime( '2022-01-29 01:00:00' );
    }

    /**
     * @inheritdoc
     */
    public function run() {
        global $wpdb;

        $table   = $wpdb->give_event_ticket_types;
        $charset = DB::get_charset_collate();

        $sql = "CREATE TABLE $table (
			id INT UNSIGNED NOT NULL AUTO_INCREMENT,
			event_id INT UNSIGNED NOT NULL,
			label TEXT NULL,
			description TEXT NULL,
			price INT UNSIGNED NULL,
			max_available INT UNSIGNED NULL,
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
