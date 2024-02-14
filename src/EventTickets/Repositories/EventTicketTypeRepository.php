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

        $createdDateTime = Temporal::withoutMicroseconds($eventTicketType->created_at ?: Temporal::getCurrentDateTime());

        DB::query('START TRANSACTION');

        try {
            DB::table('give_event_ticket_types')
                ->insert([
                    'event_id' => $eventTicketType->event_id,
                    'label' => $eventTicketType->label,
                    'description' => $eventTicketType->description,
                    'price' => $eventTicketType->price->formatToDecimal(),
                    'max_available' => $eventTicketType->max_available,
                    'created_at' => $createdDateTime,
                    'updated_at' => $createdDateTime,
                ]);

            $eventTicketType->id = DB::last_insert_id();
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

        $updatedTimeDate = Temporal::withoutMicroseconds(Temporal::getCurrentDateTime());

        DB::query('START TRANSACTION');

        try {

            DB::table('give_event_ticket_types')
                ->where('id', $eventTicketType->id)
                ->update([
                    'event_id' => $eventTicketType->event_id,
                    'label' => $eventTicketType->label,
                    'description' => $eventTicketType->description,
                    'price' => $eventTicketType->price->formatToMinorAmount(),
                    'max_available' => $eventTicketType->max_available,
                    'updated_at' => $updatedTimeDate,
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
                'label',
                'description',
                'price',
                'max_available',
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
