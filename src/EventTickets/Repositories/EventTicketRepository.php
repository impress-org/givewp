<?php

namespace Give\EventTickets\Repositories;

use Give\Donations\Models\DonationNote;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\ModelQueryBuilder;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Helpers\Hooks;
use Give\Log\Log;
use Give\EventTickets\Models\Event;
use Give\EventTickets\Models\EventTicket;
use Give\EventTickets\Models\EventTicketType;

/**
 * @unreleased
 */
class EventTicketRepository
{

    /**
     * @unreleased
     *
     * @var string[]
     */
    private $requiredProperties = [
        'eventId',
        'ticketTypeId',
        'donationId',
    ];

    /**
     * @unreleased
     *
     * @param int $id
     *
     * @return EventTicket|null
     */
    public function getById(int $id)
    {
        return $this->prepareQuery()
            ->where('id', $id)
            ->get();
    }

    /**
     * @unreleased
     *
     * @param EventTicket $eventTicket
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
                    'created_at' => $createdDateTime,
                    'updated_at' => $createdDateTime,
                ]);
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed creating an event ticket', compact('eventTicket'));

            throw new $exception('Failed creating an event ticket');
        }

        DB::query('COMMIT');

        Hooks::doAction('givewp_events_event_ticket_created', $eventTicket);
    }

    /**
     * @unreleased
     *
     * @param EventTicket $eventTicket
     *
     * @throws Exception|InvalidArgumentException
     */
    public function update(EventTicket $eventTicket)
    {
        $this->validate($eventTicket);

        Hooks::doAction('givewp_events_event_ticket_updating', $eventTicket);

        $updatedTimeDate = Temporal::withoutMicroseconds(Temporal::getCurrentDateTime());

        DB::query('START TRANSACTION');

        try {

            DB::table('give_event_tickets')
                ->where('id', $eventTicket->id)
                ->update([
                    'event_id' => $eventTicket->eventId,
                    'ticket_type_id' => $eventTicket->ticketTypeId,
                    'donation_id' => $eventTicket->donationId,
                    'updated_at' => $updatedTimeDate,
                ]);
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed updating an event ticket', compact('eventTicket'));

            throw new $exception('Failed updating an event ticket');
        }

        DB::query('COMMIT');

        Hooks::doAction('givewp_events_event_ticket_updated', $eventTicket);
    }

    /**
     * @unreleased
     *
     * @param EventTicket $eventTicket
     *
     * @return bool
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
     * @unreleased
     *
     * @param EventTicket $eventTicket
     *
     * @return void
     */
    private function validate(EventTicket $eventTicket)
    {
        foreach ($this->requiredProperties as $key) {
            if (!isset($eventTicket->$key)) {
                throw new InvalidArgumentException("'$key' is required.");
            }
        }
    }

    /**
     * @return ModelQueryBuilder<EventTicket>
     */
    public function prepareQuery(): ModelQueryBuilder
    {
        $builder = new ModelQueryBuilder(EventTicket::class);

        return $builder->from('give_event_tickets')
            ->select(
                'id',
                'event_id',
                'ticket_type_id',
                'donation_id',
                'created_at',
                'updated_at'
            );
    }

    /**
     * @unreleased
     *
     * @param int $eventId
     *
     * @return ModelQueryBuilder
     */
    public function queryByEventId(int $eventId): ModelQueryBuilder
    {
        return $this->prepareQuery()
            ->where('event_id', $eventId);
    }

    /**
     * @unreleased
     *
     * @param int $eventId
     *
     * @return ModelQueryBuilder
     */
    public function queryByTicketTypeId(int $ticketTypeId): ModelQueryBuilder
    {
        return $this->prepareQuery()
            ->where('ticket_type_id', $ticketTypeId);
    }
    /**
     * @unreleased
     *
     * @param int $donationId
     *
     * @return ModelQueryBuilder
     */
    public function queryByDonationId(int $donationId): ModelQueryBuilder
    {
        return $this->prepareQuery()
            ->where('donation_id', $donationId);
    }
}
