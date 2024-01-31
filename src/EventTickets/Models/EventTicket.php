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
use Give\EventTickets\Factories\EventTicketFactory;

/**
 * @unreleased
 */
class EventTicket extends Model implements ModelCrud /*, ModelHasFactory */
{
    /**
     * @inheritdoc
     */
    protected $properties = [
        'id' => 'int', // @todo Maybe use UUID instead of auto-incrementing integer
        'event_id' => 'int',
        'ticket_type' => 'int',
        'date_created' => DateTime::class,
    ];

    /**
     * @inheritdoc
     */
    protected $relationships = [
        'event' => Relationship::BELONGS_TO,
        'eventTicketType' => Relationship::BELONGS_TO,
    ];

    /**
     * @unreleased
     *
     * @return EventTicket|null
     */
    public static function find($id)
    {
        return give()->events->tickets->getById($id);
    }


    /**
     * @unreleased
     *
     * @return $this
     * @throws Exception|InvalidArgumentException
     */
    public static function create(array $attributes): EventTicket
    {
        $event = new static($attributes);

        give()->events->tickets->insert($event);

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
            give()->events->tickets->insert($this);
        } else{
            give()->events->tickets->update($this);
        }
    }

    /**
     * @unreleased
     *
     * @throws Exception|InvalidArgumentException
     */
    public function delete(): bool
    {
        return give()->events->tickets->delete($this);
    }

    /**
     * @unreleased
     *
     * @return ModelQueryBuilder<Event>
     */
    public static function query(): ModelQueryBuilder
    {
        return give()->events->tickets->prepareQuery();
    }

    /**
     * @unreleased
     *
     * @return ModelQueryBuilder<Event>
     */
    public function event(): ModelQueryBuilder
    {
        return give('events')->queryById($this->event_id);
    }

    /**
     * @unreleased
     *
     * @return ModelQueryBuilder<EventTicketType>
     */
    public function eventTicketType(): ModelQueryBuilder
    {
        return give('eventTicketTypes')->queryById($this->ticket_type_id);
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
            'event_id' => (int)$object->event_id,
            'ticket_type_id' => (int)$object->ticket_type_id,
            'date_created' => Temporal::toDateTime($object->date_created),
        ]);
    }

    /**
     * @unreleased
     */
    public static function factory(): EventTicketFactory
    {
        return new EventTicketFactory(static::class);
    }
}
