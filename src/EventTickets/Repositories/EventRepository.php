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
        'form_id',
    ];

    /**
     * @unreleased
     *
     * @param int $id
     *
     * @return Event|null
     */
    public function getById(int $id)
    {
        return $this->prepareQuery()
            ->where('posts.id', $id)
            ->get();
    }

    /**
     * @unreleased
     *
     * @param Event $event
     *
     * @throws Exception|InvalidArgumentException
     */
    public function insert(Event $event)
    {
        $this->validate($event);

        Hooks::doAction('givewp_events_event_creating', $event);

        $dateCreated = Temporal::withoutMicroseconds($event->createdAt ?: Temporal::getCurrentDateTime());
        $dateCreatedFormatted = Temporal::getFormattedDateTime($dateCreated);
        $dateUpdated = $donation->updatedAt ?? $dateCreated;
        $dateUpdatedFormatted = Temporal::getFormattedDateTime($dateUpdated);

        DB::query('START TRANSACTION');

        try {
            DB::table('posts')
                ->insert([
                    'post_date' => $dateCreatedFormatted,
                    'post_date_gmt' => get_gmt_from_date($dateCreatedFormatted),
                    'post_modified' => $dateUpdatedFormatted,
                    'post_modified_gmt' => get_gmt_from_date($dateUpdatedFormatted),
                    'post_status' => 'publish',
                    'post_type' => 'give_event',
                ]);

            $event->id = DB::last_insert_id();
            $event->createdAt = $dateCreated;
            $event->updatedAt = $dateUpdated;

            DB::table('give_events')
                ->insert([
                    'id' => $event->id,
                    'form_id' => $event->form_id,
                    'description' => $event->description,
                    'start_datetime' => $event->start_datetime,
                    'end_datetime' => $event->start_datetime,
                    'ticket_close_datetime' => $event->ticket_close_datetime,
                ]);
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed creating an event', compact('event'));

            throw new $exception('Failed creating an event');
        }

        DB::query('COMMIT');

        Hooks::doAction('givewp_events_event_created', $event);
    }

    /**
     * @unreleased
     *
     * @param Event $event
     *
     * @throws Exception|InvalidArgumentException
     */
    public function update(Event $event)
    {
        $this->validate($event);

        Hooks::doAction('givewp_events_event_updating', $event);

        $now = Temporal::withoutMicroseconds(Temporal::getCurrentDateTime());
        $nowFormatted = Temporal::getFormattedDateTime($now);

        DB::query('START TRANSACTION');

        try {

            DB::table('posts')
                ->where('ID', $event->id)
                ->update([
                    'post_title' => $event->title,
                    'post_content' => $event->description,
                    'post_modified' => $nowFormatted,
                    'post_modified_gmt' => get_gmt_from_date($nowFormatted),
                    'post_status' => $event->status,
                    'post_type' => 'give_event',
                ]);

            DB::table('give_events')
                ->where('id', $event->id)
                ->update([
                    'form_id' => $event->form_id,
                    'description' => $event->description,
                    'start_datetime' => $event->start_datetime,
                    'end_datetime' => $event->start_datetime,
                    'ticket_close_datetime' => $event->ticket_close_datetime,
                ]);
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed updating an event', compact('event'));

            throw new $exception('Failed updating an event');
        }

        $event->updatedAt = $now;

        DB::query('COMMIT');

        Hooks::doAction('givewp_events_event_updated', $event);
    }

    /**
     * @unreleased
     *
     * @param Event $event
     *
     * @return bool
     * @throws Exception
     */
    public function delete(Event $event): bool
    {
        DB::query('START TRANSACTION');

        Hooks::doAction('givewp_events_event_deleting', $event);

        try {
            DB::table('posts')
                ->where('id', $event->id)
                ->delete();

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
     *
     * @param Event $event
     *
     * @return void
     */
    private function validate(Event $event)
    {
        foreach ($this->requiredProperties as $key) {
            if (!isset($event->$key)) {
                throw new InvalidArgumentException("'$key' is required.");
            }
        }
    }

    /**
     * @return ModelQueryBuilder<Event>
     */
    public function prepareQuery(): ModelQueryBuilder
    {
        $builder = new ModelQueryBuilder(Event::class);

        return $builder->from('posts', 'posts')
            ->select(
                ['posts.ID', 'id'],
                ['post_title', 'title'],
                ['post_excerpt', 'description'],
                ['post_date', 'createdAt'],
                ['post_modified', 'updatedAt'],
                ['post_status', 'status']
            )
            ->leftJoin('give_events', 'posts.ID', 'events.id', 'events')
            ->where('post_type', 'give_event');
    }
}
