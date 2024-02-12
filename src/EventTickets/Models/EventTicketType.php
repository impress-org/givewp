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
use Give\EventTickets\Factories\EventTicketTypeFactory;
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
        'event_id' => 'int',
        'label' => 'string',
        'description' => 'string',
        'price' => Money::class,
        'max_available' => 'int',
        'created_at' => DateTime::class,
        'updated_at' => DateTime::class,
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
     * @return ModelQueryBuilder<Event>
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
        return give(EventRepository::class)->queryById($this->event_id);
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
    public static function fromQueryBuilderObject($object): EventTicketType
    {
        return new EventTicketType([
            'id' => (int)$object->id,
            'event_id' => (int)$object->event_id,
            'price' => Money::fromDecimal($object->price, give_get_currency()),
            'created_at' => Temporal::toDateTime($object->created_at),
            'updated_at' => Temporal::toDateTime($object->updated_at),
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
