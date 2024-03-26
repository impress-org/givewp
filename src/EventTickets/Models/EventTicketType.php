<?php

namespace Give\EventTickets\Models;

use DateTime;
use Give\EventTickets\Factories\EventTicketTypeFactory;
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
use Give\Framework\Support\ValueObjects\Money;

/**
 * @since 3.6.0
 */
class EventTicketType extends Model implements ModelCrud /*, ModelHasFactory */
{
    /**
     * @inheritdoc
     */
    protected $properties = [
        'id' => 'int',
        'eventId' => 'int',
        'title' => 'string',
        'description' => 'string',
        'price' => Money::class,
        'capacity' => 'int',
        'createdAt' => DateTime::class,
        'updatedAt' => DateTime::class,
    ];

    /**
     * @inheritdoc
     */
    protected $relationships = [
        'event' => Relationship::BELONGS_TO,
        'eventTickets' => Relationship::HAS_MANY,
    ];

    /**
     * @since 3.6.0
     */
    public static function find($id): ?EventTicketType
    {
        return give(EventTicketTypeRepository::class)->getById($id);
    }

    /**
     * @since 3.6.0
     */
    public static function findByEvent($eventId): ModelQueryBuilder
    {
        return give(EventTicketTypeRepository::class)->queryByEventId($eventId);
    }

    /**
     * @since 3.6.0
     *
     * @throws Exception|InvalidArgumentException
     */
    public static function create(array $attributes): EventTicketType
    {
        $event = new static($attributes);

        give(EventTicketTypeRepository::class)->insert($event);

        return $event;
    }

    /**
     * @since 3.6.0
     *
     * @throws Exception|InvalidArgumentException
     */
    public function save(): void
    {
        if (!$this->id) {
            give(EventTicketTypeRepository::class)->insert($this);
        } else{
            give(EventTicketTypeRepository::class)->update($this);
        }
    }

    /**
     * @since 3.6.0
     *
     * @throws Exception|InvalidArgumentException
     */
    public function delete(): bool
    {
        return give(EventTicketTypeRepository::class)->delete($this);
    }

    /**
     * @since 3.6.0
     *
     * @return ModelQueryBuilder<EventTicketType>
     */
    public static function query(): ModelQueryBuilder
    {
        return give(EventTicketTypeRepository::class)->prepareQuery();
    }

    /**
     * @since 3.6.0
     *
     * @return ModelQueryBuilder<Event>
     */
    public function event(): ModelQueryBuilder
    {
        return give(EventRepository::class)->queryById($this->eventId);
    }


    /**
     * @since 3.6.0
     *
     * @return ModelQueryBuilder<EventTicket>
     */
    public function eventTickets(): ModelQueryBuilder
    {
        return give(EventTicketRepository::class)->queryByTicketTypeId($this->id);
    }

    /**
     * @since 3.6.0
     *
     * @param object $object
     */
    public static function fromQueryBuilderObject($object): EventTicketType
    {
        return new EventTicketType([
            'id' => (int)$object->id,
            'eventId' => (int)$object->event_id,
            'title' => $object->title,
            'description' => $object->description,
            'price' => new Money($object->price, give_get_currency()),
            'capacity' => (int)$object->capacity,
            'createdAt' => Temporal::toDateTime($object->created_at),
            'updatedAt' => Temporal::toDateTime($object->updated_at),
        ]);
    }

    /**
     * @since 3.6.0
     */
    public static function factory(): EventTicketTypeFactory
    {
        return new EventTicketTypeFactory(static::class);
    }
}
