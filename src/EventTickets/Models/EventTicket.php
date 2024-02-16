<?php

namespace Give\EventTickets\Models;

use DateTime;
use Give\Donations\Models\Donation;
use Give\EventTickets\Factories\EventTicketFactory;
use Give\EventTickets\Repositories\EventRepository;
use Give\EventTickets\Repositories\EventTicketRepository;
use Give\EventTickets\Repositories\EventTicketTypeRepository;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\Contracts\ModelCrud;
use Give\Framework\Models\Model;
use Give\Framework\Models\ModelQueryBuilder;
use Give\Framework\Models\ValueObjects\Relationship;
use Give\Framework\Support\Facades\DateTime\Temporal;

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
        'eventId' => 'int',
        'ticketTypeId' => 'int',
        'donationId' => 'int',
        'createdAt' => DateTime::class,
        'updatedAt' => DateTime::class,
    ];

    /**
     * @inheritdoc
     */
    protected $relationships = [
        'event' => Relationship::BELONGS_TO,
        'ticketType' => Relationship::BELONGS_TO,
        'donation' => Relationship::BELONGS_TO,
    ];

    /**
     * @unreleased
     *
     * @return EventTicket|null
     */
    public static function find($id)
    {
        return give(EventTicketRepository::class)->getById($id);
    }

    /**
     * @unreleased
     *
     * @return ModelQueryBuilder
     */
    public static function findByEvent($eventId): ModelQueryBuilder
    {
        return give(EventTicketRepository::class)->queryByEventId($eventId);
    }


    /**
     * @unreleased
     *
     * @return ModelQueryBuilder
     */
    public static function findByTicketType($ticketTypeId): ModelQueryBuilder
    {
        return give(EventTicketRepository::class)->queryByTicketTypeId($ticketTypeId);
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

        give(EventTicketRepository::class)->insert($event);

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
            give(EventTicketRepository::class)->insert($this);
        } else{
            give(EventTicketRepository::class)->update($this);
        }
    }

    /**
     * @unreleased
     *
     * @throws Exception|InvalidArgumentException
     */
    public function delete(): bool
    {
        return give(EventTicketRepository::class)->delete($this);
    }

    /**
     * @unreleased
     *
     * @return ModelQueryBuilder<EventTicket>
     */
    public static function query(): ModelQueryBuilder
    {
        return give(EventTicketRepository::class)->prepareQuery();
    }

    /**
     * @unreleased
     *
     * @return ModelQueryBuilder<Event>
     */
    public function event(): ModelQueryBuilder
    {
        return give(EventRepository::class)->queryById($this->eventId);
    }

    /**
     * @unreleased
     *
     * @return ModelQueryBuilder<EventTicketType>
     */
    public function ticketType(): ModelQueryBuilder
    {
        return give(EventTicketTypeRepository::class)->queryById($this->ticketTypeId);
    }


    /**
     * @unreleased
     *
     * @return ModelQueryBuilder<Donation>
     */
    public function donation(): ModelQueryBuilder
    {
        return give()->donations->queryById($this->donationId);
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
            'eventId' => (int)$object->event_id,
            'ticketTypeId' => (int)$object->ticket_type_id,
            'donationId' => (int)$object->donation_id,
            'createdAt' => Temporal::toDateTime($object->created_at),
            'updatedAt' => Temporal::toDateTime($object->updated_at),
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
