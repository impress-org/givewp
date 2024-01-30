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
use GiveEvents\Events\Factories\EventFactory;

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
        'form_id' => 'int',
        'title' => 'string',
        'description' => 'string',
        'start_datetime' => DateTime::class,
        'end_datetime' => DateTime::class,
        'ticket_close_datetime' => DateTime::class,
        'createdAt' => DateTime::class,
        'updatedAt' => DateTime::class,
        'status' => 'string',
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
        return give()->events->getById($id);
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

        give()->events->insert($event);

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
            give()->events->insert($this);
        } else{
            give()->events->update($this);
        }
    }

    /**
     * @unreleased
     *
     * @throws Exception|InvalidArgumentException
     */
    public function delete(): bool
    {
        return give()->events->delete($this);
    }

    /**
     * @unreleased
     *
     * @return ModelQueryBuilder<Event>
     */
    public static function query(): ModelQueryBuilder
    {
        return give('events')->prepareQuery();
    }

    /**
     * @unreleased
     *
     * @return ModelQueryBuilder<EventTicketType>
     */
    public function eventTicketTypes(): ModelQueryBuilder
    {
        return give('eventTicketTypes')->queryByEventId($this->id);
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
    public static function fromQueryBuilderObject($object): Event
    {
        return new Event([
            'id' => (int)$object->id,
            'form_id' => (int)$object->form_id,
            'title' => (string)$object->title,
            'description' => (string)$object->description,
            'createdAt' => Temporal::toDateTime($object->createdAt),
            'updatedAt' => Temporal::toDateTime($object->updatedAt),
            'start_datetime' => $object->start_datetime ? Temporal::toDateTime($object->start_datetime) : null,
            'end_datetime' => $object->end_datetime ? Temporal::toDateTime($object->end_datetime) : null,
            'ticket_close_datetime' => $object->ticket_close_datetime ? Temporal::toDateTime($object->ticket_close_datetime) :null,
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
