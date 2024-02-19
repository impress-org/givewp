<?php

namespace Give\EventTickets\Repositories;

use Give\EventTickets\Models\Event;
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
class EventRepository
{

    /**
     * @unreleased
     *
     * @var string[]
     */
    private $requiredProperties = [
        'title',
        'startDateTime',
    ];

    /**
     * @unreleased
     */
    public function getById(int $id): ?Event
    {
        return $this->prepareQuery()
            ->where('id', $id)
            ->get();
    }

    /**
     * @unreleased
     *
     * @throws Exception|InvalidArgumentException
     */
    public function insert(Event $event): void
    {
        $this->validate($event);

        Hooks::doAction('givewp_events_event_creating', $event);

        $createdDateTime = Temporal::withoutMicroseconds($event->createdAt ?: Temporal::getCurrentDateTime());

        DB::query('START TRANSACTION');

        try {
            DB::table('give_events')
                ->insert([
                    'title' => $event->title,
                    'description' => $event->description,
                    'start_datetime' => $event->startDateTime->format('Y-m-d H:i:s'),
                    'end_datetime' => $event->endDateTime ? $event->endDateTime->format('Y-m-d H:i:s') : null,
                    'created_at' => $createdDateTime->format('Y-m-d H:i:s'),
                    'updated_at' => $createdDateTime->format('Y-m-d H:i:s'),
                ]);

            $eventId = DB::last_insert_id();
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed creating an event', compact('event'));

            throw new $exception('Failed creating an event');
        }

        $event->id = $eventId;
        $event->createdAt = $createdDateTime;
        $event->updatedAt = $createdDateTime;

        DB::query('COMMIT');

        Hooks::doAction('givewp_events_event_created', $event);
    }

    /**
     * @unreleased
     *
     * @throws Exception|InvalidArgumentException
     */
    public function update(Event $event): void
    {
        $this->validate($event);

        Hooks::doAction('givewp_events_event_updating', $event);

        $updatedDateTime = Temporal::withoutMicroseconds(Temporal::getCurrentDateTime());

        DB::query('START TRANSACTION');

        try {
            DB::table('give_events')
                ->where('id', $event->id)
                ->update([
                    'description' => $event->description,
                    'start_datetime' => $event->startDateTime->format('Y-m-d H:i:s'),
                    'end_datetime' => $event->end_datetime ? $event->end_datetime->format('Y-m-d H:i:s') : null,
                    'updated_at' => $updatedDateTime->format('Y-m-d H:i:s'),
                ]);
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed updating an event', compact('event'));

            throw new $exception('Failed updating an event');
        }

        $event->updatedAt = $updatedDateTime;

        DB::query('COMMIT');

        Hooks::doAction('givewp_events_event_updated', $event);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function delete(Event $event): bool
    {
        DB::query('START TRANSACTION');

        Hooks::doAction('givewp_events_event_deleting', $event);

        try {
            DB::table('give_events')
                ->where('id', $event->id)
                ->delete();
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed deleting an event', compact('event'));

            throw new $exception('Failed deleting an event');
        }

        DB::query('COMMIT');

        Hooks::doAction('givewp_events_event_deleted', $event);

        return true;
    }

    /**
     * @unreleased
     */
    private function validate(Event $event): void
    {
        foreach ($this->requiredProperties as $key) {
            if (!isset($event->$key)) {
                throw new InvalidArgumentException("'$key' is required.");
            }
        }
    }

    /**
     * @unreleased
     *
     * @return ModelQueryBuilder<Event>
     */
    public function prepareQuery(): ModelQueryBuilder
    {
        $builder = new ModelQueryBuilder(Event::class);

        return $builder->from('give_events')
            ->select(
                'id',
                'title',
                'description',
                'start_datetime',
                'end_datetime',
                'created_at',
                'updated_at'
            );
    }
}
