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
use Give\EventTickets\Models\EventTicketType;

/**
 * @unreleased
 */
class EventTicketTypeRepository
{

    /**
     * @unreleased
     *
     * @var string[]
     */
    private $requiredProperties = [
        'event_id',
    ];

    /**
     * @unreleased
     *
     * @param int $id
     *
     * @return EventTicketType|null
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
     * @param EventTicketType $eventTicketType
     *
     * @throws Exception|InvalidArgumentException
     */
    public function insert(EventTicketType $eventTicketType)
    {
        $this->validate($eventTicketType);

        Hooks::doAction('givewp_events_event_ticket_type_creating', $eventTicketType);

        $dateCreated = Temporal::withoutMicroseconds($eventTicketType->date_created ?: Temporal::getCurrentDateTime());

        DB::query('START TRANSACTION');

        try {
            DB::table('give_event_ticket_types')
                ->insert([
                    'id' => $eventTicketType->id,
                    'event_id' => $eventTicketType->event_id,
                    'date_created' => $dateCreated,
                ]);
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed creating an event ticket type', compact('eventTicketType'));

            throw new $exception('Failed creating an event ticket type');
        }

        DB::query('COMMIT');

        Hooks::doAction('givewp_events_event_ticket_type_created', $eventTicketType);
    }

    /**
     * @unreleased
     *
     * @param EventTicketType $eventTicketType
     *
     * @throws Exception|InvalidArgumentException
     */
    public function update(EventTicketType $eventTicketType)
    {
        $this->validate($eventTicketType);

        Hooks::doAction('givewp_events_event_ticket_type_updating', $eventTicketType);

        DB::query('START TRANSACTION');

        try {

            DB::table('give_event_ticket_types')
                ->where('id', $eventTicketType->id)
                ->update([
                    'event_id' => $eventTicketType->event_id,
                ]);
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed updating an event ticket type', compact('eventTicketType'));

            throw new $exception('Failed updating an event ticket type');
        }

        DB::query('COMMIT');

        Hooks::doAction('givewp_events_event_ticket_type_updated', $eventTicketType);
    }

    /**
     * @unreleased
     *
     * @param EventTicketType $eventTicketType
     *
     * @return bool
     * @throws Exception
     */
    public function delete(EventTicketType $eventTicketType): bool
    {
        DB::query('START TRANSACTION');

        Hooks::doAction('givewp_events_event_ticket_type_deleting', $eventTicketType);

        try {
            DB::table('give_event_ticket_types')
                ->where('id', $eventTicketType->id)
                ->delete();
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed deleting an event ticket type', compact('eventTicketType'));

            throw new $exception('Failed deleting an event ticket type');
        }

        DB::query('COMMIT');

        Hooks::doAction('givewp_events_event_ticket_type_deleted', $eventTicketType);

        return true;
    }

    /**
     * @unreleased
     *
     * @param EventTicketType $eventTicketType
     *
     * @return void
     */
    private function validate(EventTicketType $eventTicketType)
    {
        foreach ($this->requiredProperties as $key) {
            if (!isset($eventTicketType->$key)) {
                throw new InvalidArgumentException("'$key' is required.");
            }
        }
    }

    /**
     * @return ModelQueryBuilder<EventTicketType>
     */
    public function prepareQuery(): ModelQueryBuilder
    {
        $builder = new ModelQueryBuilder(EventTicketType::class);

        return $builder->from('give_event_ticket_types')
            ->select(
                'id',
                'event_id',
                'date_created'
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
}
