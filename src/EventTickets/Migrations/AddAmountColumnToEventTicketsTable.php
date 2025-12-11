<?php

namespace Give\EventTickets\Migrations;

use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;

/**
 * @since 3.20.0
 */
class AddAmountColumnToEventTicketsTable extends Migration
{
    /**
     * @inheritdoc
     *
     * @since 3.20.0
     */
    public static function id()
    {
        return 'give-events-add-amount-column-to-events-tickets-table';
    }

    /**
     * @since 3.20.0
     */
    public static function title()
    {
        return 'Add "amount" column to give_event_tickets table';
    }

    /**
     * @inheritdoc
     *
     * @since 3.20.0
     */
    public static function timestamp()
    {
        return strtotime('2022-03-18 12:00:00');
    }

    /**
     * @inheritdoc
     *
     * @since 3.20.0
     *
     * @throws DatabaseMigrationException
     */
    public function run()
    {
        global $wpdb;

        $eventTicketsTable = $wpdb->give_event_tickets;
        $eventTicketTypesTable = $wpdb->give_event_ticket_types;

        $this->addAmountColumn($wpdb, $eventTicketsTable);
        $this->migrateTicketPrices($wpdb, $eventTicketsTable, $eventTicketTypesTable);
    }

    /**
     * @since 3.20.0
     *
     * @throws DatabaseMigrationException
     */
    private function addAmountColumn($wpdb, $eventTicketsTable)
    {
        $sql = "ALTER TABLE $eventTicketsTable
                ADD COLUMN amount INT UNSIGNED NOT NULL AFTER donation_id";

        try {
            maybe_add_column($eventTicketsTable, 'amount', $sql);
        } catch (DatabaseQueryException $exception) {
            throw new DatabaseMigrationException("An error occurred while adding the amount column to the $eventTicketsTable table", 0, $exception);
        }
    }

    /**
     * @since 3.20.0
     *
     * @throws DatabaseMigrationException
     */
    private function migrateTicketPrices($wpdb, $eventTicketsTable, $eventTicketTypesTable)
    {
        $sql = "UPDATE $eventTicketsTable eventTickets
                JOIN $eventTicketTypesTable evenTicketTypes
                ON eventTickets.ticket_type_id = evenTicketTypes.id
                SET eventTickets.amount = evenTicketTypes.price";

        try {
            $wpdb->query($sql);
        } catch (DatabaseQueryException $exception) {
            throw new DatabaseMigrationException("An error occurred while migrating data to the amount column in the $eventTicketsTable table", 0, $exception);
        }
    }
};
