<?php

namespace Give\EventTickets\Models;

use DateTime;
use Give\Donations\Factories\DonationNoteFactory;
use Give\Donations\ValueObjects\DonationNoteType;
use Give\EventTickets\Repositories\EventRepository;
use Give\EventTickets\Repositories\EventTicketRepository;
use Give\EventTickets\Repositories\EventTicketTypeRepository;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\Contracts\ModelCrud;
use Give\Framework\Models\Contracts\ModelHasFactory;
use Give\Framework\Models\Model;
use Give\Framework\Models\ModelQueryBuilder;
use Give\Framework\Models\ValueObjects\Relationship;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\EventTickets\Factories\EventFactory;

/**
 * @unreleased
 */
class Event extends Model implements ModelCrud /*, ModelHasFactory */
{
    /**
     * @inheritdoc
     */
    protected $properties = [
        'id' => 'int',
        'title' => 'string',
        'description' => 'string',
        'start_datetime' => DateTime::class,
        'end_datetime' => DateTime::class,
        'ticket_close_datetime' => DateTime::class,
        'created_at' => DateTime::class,
        'updated_at' => DateTime::class,
    ];

    /**
     * @inheritdoc
     */
    protected $relationships = [
        'tickets' => Relationship::HAS_MANY,
        'ticketTypes' => Relationship::HAS_MANY,
    ];

    /**
     * @unreleased
     *
     * @return Event|null
     */
    public static function find($id)
    {
        return give(EventRepository::class)->getById($id);
    }


    /**
     * @unreleased
     *
     * @return $this
     * @throws Exception|InvalidArgumentException
     */
    public static function create(array $attributes): Event
    {
        $event = new static($attributes);

        give(EventRepository::class)->insert($event);

        return $event;
    }

    /**
     * @unreleased
     *
     * @return void
     * @throws Exception|InvalidArgumentException
     */
    public function save()
    {
        if (!$this->id) {
            give(EventRepository::class)->insert($this);
        } else{
            give(EventRepository::class)->update($this);
        }
    }

    /**
     * @unreleased
     *
     * @throws Exception|InvalidArgumentException
     */
    public function delete(): bool
    {
        return give(EventRepository::class)->delete($this);
    }

    /**
     * @unreleased
     *
     * @return ModelQueryBuilder<Event>
     */
    public static function query(): ModelQueryBuilder
    {
        return give(EventRepository::class)->prepareQuery();
    }

    /**
     * @unreleased
     *
     * @return ModelQueryBuilder<EventTicketType>
     */
    public function ticketTypes(): ModelQueryBuilder
    {
        return give(EventTicketTypeRepository::class)->queryByEventId($this->id);
    }

    /**
     * @unreleased
     *
     * @return ModelQueryBuilder<EventTicket>
     */
    public function eventTickets(): ModelQueryBuilder
    {
        return give(EventTicketRepository::class)->queryByEventId($this->id);
    }

    /**
     * @unreleased
     *
     * @param object $object
     */
    public static function fromQueryBuilderObject($object): Event
    {
        return new Event([
            'id' => (int)$object->id,
            'title' => (string)$object->title,
            'description' => (string)$object->description,
            'start_datetime' => $object->start_datetime ? Temporal::toDateTime($object->start_datetime) : null,
            'end_datetime' => $object->end_datetime ? Temporal::toDateTime($object->end_datetime) : null,
            'ticket_close_datetime' => $object->ticket_close_datetime ? Temporal::toDateTime($object->ticket_close_datetime) :null,
            'created_at' => Temporal::toDateTime($object->created_at),
            'updated_at' => Temporal::toDateTime($object->updated_at),
        ]);
    }

    /**
     * @unreleased
     */
    public static function factory(): EventFactory
    {
        return new EventFactory(static::class);
    }
}
