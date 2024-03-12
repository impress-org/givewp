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
 * @unreleased
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
     * @unreleased
     */
    public static function find($id): ?EventTicketType
    {
        return give(EventTicketTypeRepository::class)->getById($id);
    }

    /**
     * @unreleased
     */
    public static function findByEvent($eventId): ModelQueryBuilder
    {
        return give(EventTicketTypeRepository::class)->queryByEventId($eventId);
    }

    /**
     * @unreleased
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
     * @unreleased
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
     * @unreleased
     *
     * @throws Exception|InvalidArgumentException
     */
    public function delete(): bool
    {
        return give(EventTicketTypeRepository::class)->delete($this);
    }

    /**
     * @unreleased
     *
     * @return ModelQueryBuilder<EventTicketType>
     */
    public static function query(): ModelQueryBuilder
    {
        return give(EventTicketTypeRepository::class)->prepareQuery();
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
     * @return ModelQueryBuilder<EventTicket>
     */
    public function eventTickets(): ModelQueryBuilder
    {
        return give(EventTicketRepository::class)->queryByTicketTypeId($this->id);
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
            'eventId' => (int)$object->event_id,
            'title' => $object->title,
            'description' => $object->description,
            'price' => new Money($object->price, give_get_currency()),
            'capacity' => ! is_null($object->capacity) ? (int)$object->capacity : null,
            'createdAt' => Temporal::toDateTime($object->created_at),
            'updatedAt' => Temporal::toDateTime($object->updated_at),
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
