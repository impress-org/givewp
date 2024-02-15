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
        'label' => 'string',
        'description' => 'string',
        'price' => Money::class,
        'totalTickets' => 'int',
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
     *
     * @return EventTicketType|null
     */
    public static function find($id)
    {
        return give(EventTicketTypeRepository::class)->getById($id);
    }

    /**
     * @unreleased
     *
     * @return ModelQueryBuilder
     */
    public static function findByEvent($eventId): ModelQueryBuilder
    {
        return give(EventRepository::class)->queryByEventId($eventId);
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

        give(EventTicketTypeRepository::class)->insert($event);

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
        return give(EventTicketRepository::class)->queryByEventId($this->eventId);
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
            'label' => $object->label,
            'description' => $object->description,
            'price' => Money::fromDecimal($object->price, give_get_currency()),
            'maxAvailable' => (int)$object->max_available,
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
