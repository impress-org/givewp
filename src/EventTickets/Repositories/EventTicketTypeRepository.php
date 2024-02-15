<?php

namespace Give\EventTickets\Repositories;

use Give\EventTickets\Models\EventTicketType;
use Give\EventTickets\ValueObjects\EventTicketTypeStatus;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\ModelQueryBuilder;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Helpers\Hooks;
use Give\Log\Log;

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
        'eventId',
        'label',
        'price',
        'ticketsTotal',
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

        $createdDateTime = Temporal::withoutMicroseconds($eventTicketType->createdAt ?: Temporal::getCurrentDateTime());
        $status = $eventTicketType->status ? $eventTicketType->status->getValue() : EventTicketTypeStatus::ENABLED();

        DB::query('START TRANSACTION');

        try {
            DB::table('give_event_ticket_types')
                ->insert([
                    'event_id' => $eventTicketType->eventId,
                    'label' => $eventTicketType->label,
                    'description' => $eventTicketType->description,
                    'price' => $eventTicketType->price->formatToDecimal(),
                    'max_tickets_available' => $eventTicketType->maxTicketsAvailable,
                    'status' => $status,
                    'created_at' => $createdDateTime,
                    'updated_at' => $createdDateTime,
                ]);

            $eventTicketTypeId = DB::last_insert_id();
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed creating an event ticket type', compact('eventTicketType'));

            throw new $exception('Failed creating an event ticket type');
        }

        $eventTicketType->id = $eventTicketTypeId;
        $eventTicketType->createdAt = $createdDateTime;
        $eventTicketType->updatedAt = $createdDateTime;

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

        $updatedDateTime = Temporal::withoutMicroseconds(Temporal::getCurrentDateTime());
        $status = $eventTicketType->status ? $eventTicketType->status->getValue() : EventTicketTypeStatus::ENABLED();

        DB::query('START TRANSACTION');

        try {

            DB::table('give_event_ticket_types')
                ->where('id', $eventTicketType->id)
                ->update([
                    'event_id' => $eventTicketType->eventId,
                    'label' => $eventTicketType->label,
                    'description' => $eventTicketType->description,
                    'price' => $eventTicketType->price->formatToDecimal(),
                    'max_tickets_available' => $eventTicketType->maxTicketsAvailable,
                    'status' => $status,
                    'updated_at' => $updatedDateTime,
                ]);
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed updating an event ticket type', compact('eventTicketType'));

            throw new $exception('Failed updating an event ticket type');
        }

        $eventTicketType->updatedAt = $updatedDateTime;

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
                'label',
                'description',
                'price',
                'total_tickets',
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
}
