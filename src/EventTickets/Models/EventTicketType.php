<?php

namespace Give\EventTickets\Models;

use DateTime;
use Give\Donations\Factories\DonationNoteFactory;
use Give\Donations\ValueObjects\DonationNoteType;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\Contracts\ModelCrud;
use Give\Framework\Models\Contracts\ModelHasFactory;
use Give\Framework\Models\Model;
use Give\Framework\Models\ModelQueryBuilder;
use Give\Framework\Models\ValueObjects\Relationship;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\EventTickets\Factories\EventTicketTypeFactory;

/**
 * @unreleased
 */
class EventTicketType extends Model implements ModelCrud /*, ModelHasFactory */
{
    /**
     * @inheritdoc
     */
    protected $properties = [
        'id' => 'int',
        'event_id' => 'int',
        'date_created' => DateTime::class,
    ];

    /**
     * @inheritdoc
     */
    protected $relationships = [
        'event' => Relationship::BELONGS_TO,
        'eventTickets' => Relationship::HAS_MANY,
    ];

    /**
     * @unreleased
     *
     * @return EventTicketType|null
     */
    public static function find($id)
    {
        return give('eventTicketTypes')->getById($id);
    }

    /**
     * @unreleased
     *
     * @return ModelQueryBuilder
     */
    public static function findByEvent($eventId): ModelQueryBuilder
    {
        return give()->events->tickets->queryByEventId($eventId);
    }

    /**
     * @unreleased
     *
     * @return $this
     * @throws Exception|InvalidArgumentException
     */
    public static function create(array $attributes): EventTicketType
    {
        $event = new static($attributes);

        give('eventTicketTypes')->insert($event);

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
            give('eventTicketTypes')->insert($this);
        } else{
            give('eventTicketTypes')->update($this);
        }
    }

    /**
     * @unreleased
     *
     * @throws Exception|InvalidArgumentException
     */
    public function delete(): bool
    {
        return give('eventTicketTypes')->delete($this);
    }

    /**
     * @unreleased
     *
     * @return ModelQueryBuilder<Event>
     */
    public static function query(): ModelQueryBuilder
    {
        return give('eventTicketTypes')->prepareQuery();
    }

    /**
     * @unreleased
     *
     * @return ModelQueryBuilder<Event>
     */
    public function event(): ModelQueryBuilder
    {
        return give()->events->queryById($this->event_id);
    }


    /**
     * @unreleased
     *
     * @return ModelQueryBuilder<EventTicket>
     */
    public function eventTickets(): ModelQueryBuilder
    {
        return give('eventTickets')->queryByEventId($this->id);
    }

    /**
     * @unreleased
     *
     * @param object $object
     */
    public static function fromQueryBuilderObject($object): EventTicketType
    {
        return new EventTicketType([
            'id' => (int)$object->id,
            'event_id' => (int)$object->event_id,
            'date_created' => $object->date_created ? Temporal::toDateTime($object->date_created) : null,
        ]);
    }

    /**
     * @unreleased
     */
    public static function factory(): EventTicketTypeFactory
    {
        return new EventTicketTypeFactory(static::class);
    }
}
