<?php

namespace Give\EventTickets\Repositories;

use Give\BetaFeatures\Facades\FeatureFlag;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\EventTickets\Models\EventTicket;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\ModelQueryBuilder;
use Give\Framework\QueryBuilder\JoinQueryBuilder;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Framework\Support\ValueObjects\Money;
use Give\Helpers\Hooks;
use Give\Helpers\Table;
use Give\Log\Log;

/**
 * @since 3.6.0
 */
class EventTicketRepository
{

    /**
     * @since 3.20.0 Add "amount" column to the properties array
     * @since 3.6.0
     *
     * @var string[]
     */
    private $requiredProperties = [
        'eventId',
        'ticketTypeId',
        'donationId',
        'amount',
    ];

    /**
     * @since 3.6.0
     */
    public function getById(int $id): ?EventTicket
    {
        return $this->prepareQuery()
            ->where('id', $id)
            ->get();
    }

    /**
     * @since 3.6.0
     */
    public function queryById(int $id): ModelQueryBuilder
    {
        return $this->prepareQuery()
            ->where('id', $id);
    }

    /**
     * @since 3.20.0 Add "amount" column to the insert statement
     * @since 3.6.0
     *
     * @throws Exception|InvalidArgumentException
     */
    public function insert(EventTicket $eventTicket)
    {
        $this->validate($eventTicket);

        Hooks::doAction('givewp_events_event_ticket_creating', $eventTicket);

        $createdDateTime = Temporal::withoutMicroseconds($eventTicket->createdAt ?: Temporal::getCurrentDateTime());

        DB::query('START TRANSACTION');

        try {
            DB::table('give_event_tickets')
                ->insert([
                    'event_id' => $eventTicket->eventId,
                    'ticket_type_id' => $eventTicket->ticketTypeId,
                    'donation_id' => $eventTicket->donationId,
                    'amount' => $eventTicket->amount->formatToMinorAmount(),
                    'created_at' => $createdDateTime->format('Y-m-d H:i:s'),
                    'updated_at' => $createdDateTime->format('Y-m-d H:i:s'),
                ]);

            $eventTicketId = DB::last_insert_id();
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed creating an event ticket', compact('eventTicket'));

            throw new $exception('Failed creating an event ticket');
        }

        $eventTicket->id = $eventTicketId;
        $eventTicket->createdAt = $createdDateTime;
        $eventTicket->updatedAt = $createdDateTime;

        DB::query('COMMIT');

        Hooks::doAction('givewp_events_event_ticket_created', $eventTicket);
    }

    /**
     * @since 3.20.0 Add "amount" column to the update statement
     * @since 3.6.0
     *
     * @throws Exception|InvalidArgumentException
     */
    public function update(EventTicket $eventTicket)
    {
        $this->validate($eventTicket);

        Hooks::doAction('givewp_events_event_ticket_updating', $eventTicket);

        $updatedDateTime = Temporal::withoutMicroseconds(Temporal::getCurrentDateTime());

        DB::query('START TRANSACTION');

        try {

            DB::table('give_event_tickets')
                ->where('id', $eventTicket->id)
                ->update([
                    'event_id' => $eventTicket->eventId,
                    'ticket_type_id' => $eventTicket->ticketTypeId,
                    'donation_id' => $eventTicket->donationId,
                    'amount' => $eventTicket->amount->formatToMinorAmount(),
                    'updated_at' => $updatedDateTime->format('Y-m-d H:i:s'),
                ]);
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed updating an event ticket', compact('eventTicket'));

            throw new $exception('Failed updating an event ticket');
        }

        $eventTicket->updatedAt = $updatedDateTime;

        DB::query('COMMIT');

        Hooks::doAction('givewp_events_event_ticket_updated', $eventTicket);
    }

    /**
     * @since 3.6.0
     *
     * @throws Exception
     */
    public function delete(EventTicket $eventTicket): bool
    {
        DB::query('START TRANSACTION');

        Hooks::doAction('givewp_events_event_ticket_deleting', $eventTicket);

        try {
            DB::table('give_event_tickets')
                ->where('id', $eventTicket->id)
                ->delete();
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed deleting an event ticket', compact('eventTicket'));

            throw new $exception('Failed deleting an event ticket');
        }

        DB::query('COMMIT');

        Hooks::doAction('givewp_events_event_ticket_deleted', $eventTicket);

        return true;
    }

    /**
     * @since 3.6.0
     */
    private function validate(EventTicket $eventTicket): void
    {
        foreach ($this->requiredProperties as $key) {
            if (!isset($eventTicket->$key)) {
                throw new InvalidArgumentException("'$key' is required.");
            }
        }
    }

    /**
     * @unreleased Add support for feature flag when disabled and include donation currency
     * @since 3.20.0 Add "amount" column to the select statement
     * @since      3.6.0
     * @return ModelQueryBuilder<EventTicket>
     */
    public function prepareQuery(): ModelQueryBuilder
    {
        $builder = new ModelQueryBuilder(EventTicket::class);

        if (!FeatureFlag::eventTickets()) {
            // Check if the table exists when feature flag is disabled
            if (!$this->tableExists('give_event_tickets')) {
                // Return a query builder that safely returns empty results using a table that always exists
                // We use the posts table with an impossible condition to ensure 0 results and countability
                global $wpdb;
                return $builder->from($wpdb->posts)
                    ->select('NULL as id', 'NULL as event_id', 'NULL as ticket_type_id', 'NULL as donation_id', 'NULL as amount', 'NULL as created_at', 'NULL as updated_at')
                    ->where('1', '0'); // This ensures no results are returned but queries remain countable
            }

            return $builder->from('give_event_tickets');
        }

        return $builder->from('give_event_tickets', 'tickets')
            ->select(
                ['tickets.id', 'id'],
                ['tickets.event_id', 'event_id'],
                ['tickets.ticket_type_id', 'ticket_type_id'],
                ['tickets.amount', 'amount'],
                ['tickets.created_at', 'created_at'],
                ['tickets.updated_at', 'updated_at'],
            )
            ->selectRaw("tickets.donation_id as donation_id")
            ->attachMeta(
                 'give_donationmeta',
                 'tickets.donation_id',
                 'donation_id',
                 [DonationMetaKeys::CURRENCY, 'currency']
             );
    }

    /**
     * Check if a database table exists
     *
     * @unreleased
     * @param string $tableName
     * @return bool
     */
    private function tableExists(string $tableName): bool
    {
        global $wpdb;

        $prefixedTableName = $wpdb->prefix . $tableName;
        $query = $wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($prefixedTableName));

        return (bool) $wpdb->get_var($query);
    }

    /**
     * @since 3.6.0
     */
    public function queryByEventId(int $eventId): ModelQueryBuilder
    {
        return $this->prepareQuery()
            ->where('tickets.event_id', $eventId);
    }

    /**
     * @since 3.6.0
     */
    public function queryByTicketTypeId(int $ticketTypeId): ModelQueryBuilder
    {
        return $this->prepareQuery()
            ->where('tickets.ticket_type_id', $ticketTypeId);
    }

    /**
     * @since 3.6.0
     *
     * @param int $donationId
     *
     * @return ModelQueryBuilder
     */
    public function queryByDonationId(int $donationId): ModelQueryBuilder
    {
        return $this->prepareQuery()
            ->where('tickets.donation_id', $donationId);
    }

    /**
     * @unreleased Ensure the currency is the same as the donation amount currency
     * @since 3.20.0 Refactored to use event ticket amount instead of ticket type price
     * @since 3.6.0
     */
    public function getTotalByDonation(Donation $donation): Money
    {
        $eventTickets = $this->queryByDonationId($donation->id)->getAll() ?? [];

        return array_reduce($eventTickets, static function (Money $carry, EventTicket $eventTicket) {
            return $carry->add($eventTicket->amount);
        }, new Money(0, $donation->amount->getCurrency()));
    }

    /**
     * @unreleased
     */
    public function getEventTicketDetails(Donation $donation): array
    {
        $details = [];
        $eventTickets = $this->queryByDonationId($donation->id)->getAll() ?? [];

        foreach ($eventTickets as $eventTicket) {
            $details[] = array_merge($eventTicket->toArray(), [
                'event' => $eventTicket->event->toArray(),
                'ticketType' => $eventTicket->ticketType->toArray(),
            ]);
        }

        return $details;
    }
}
