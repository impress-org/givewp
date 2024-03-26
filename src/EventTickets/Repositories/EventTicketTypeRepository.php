<?php

namespace Give\EventTickets\Repositories;

use Give\EventTickets\Models\EventTicketType;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\ModelQueryBuilder;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Helpers\Hooks;
use Give\Log\Log;

/**
 * @since 3.6.0
 */
class EventTicketTypeRepository
{

    /**
     * @since 3.6.0
     *
     * @var string[]
     */
    private $requiredProperties = [
        'eventId',
        'title',
        'price',
        'capacity',
    ];

    /**
     * @since 3.6.0
     */
    public function getById(int $id): ?EventTicketType
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
     * @since 3.6.0
     *
     * @throws Exception|InvalidArgumentException
     */
    public function insert(EventTicketType $eventTicketType): void
    {
        $this->validate($eventTicketType);

        Hooks::doAction('givewp_events_event_ticket_type_creating', $eventTicketType);

        $createdDateTime = Temporal::withoutMicroseconds($eventTicketType->createdAt ?: Temporal::getCurrentDateTime());

        DB::query('START TRANSACTION');

        try {
            DB::table('give_event_ticket_types')
                ->insert([
                    'event_id' => $eventTicketType->eventId,
                    'title' => $eventTicketType->title,
                    'description' => $eventTicketType->description,
                    'price' => $eventTicketType->price->formatToMinorAmount(),
                    'capacity' => $eventTicketType->capacity,
                    'created_at' => $createdDateTime->format('Y-m-d H:i:s'),
                    'updated_at' => $createdDateTime->format('Y-m-d H:i:s'),
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
     * @since 3.6.0
     *
     * @throws Exception|InvalidArgumentException
     */
    public function update(EventTicketType $eventTicketType): void
    {
        $this->validate($eventTicketType);

        Hooks::doAction('givewp_events_event_ticket_type_updating', $eventTicketType);

        $updatedDateTime = Temporal::withoutMicroseconds(Temporal::getCurrentDateTime());

        DB::query('START TRANSACTION');

        try {

            DB::table('give_event_ticket_types')
                ->where('id', $eventTicketType->id)
                ->update([
                    'event_id' => $eventTicketType->eventId,
                    'title' => $eventTicketType->title,
                    'description' => $eventTicketType->description,
                    'price' => $eventTicketType->price->formatToMinorAmount(),
                    'capacity' => $eventTicketType->capacity,
                    'updated_at' => $updatedDateTime->format('Y-m-d H:i:s'),
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
     * @since 3.6.0
     *
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
     * @since 3.6.0
     */
    private function validate(EventTicketType $eventTicketType): void
    {
        foreach ($this->requiredProperties as $key) {
            if (!isset($eventTicketType->$key)) {
                throw new InvalidArgumentException("'$key' is required.");
            }
        }
    }

    /**
     * @since 3.6.0
     *
     * @return ModelQueryBuilder<EventTicketType>
     */
    public function prepareQuery(): ModelQueryBuilder
    {
        $builder = new ModelQueryBuilder(EventTicketType::class);

        return $builder->from('give_event_ticket_types')
            ->select(
                'id',
                'event_id',
                'title',
                'description',
                'price',
                'capacity',
                'created_at',
                'updated_at'
            );
    }

    /**
     * @since 3.6.0
     */
    public function queryByEventId(int $eventId): ModelQueryBuilder
    {
        return $this->prepareQuery()
            ->where('event_id', $eventId);
    }
}
