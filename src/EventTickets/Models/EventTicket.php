<?php

namespace Give\EventTickets\Models;

use DateTime;
use Give\Donations\Factories\DonationNoteFactory;
use Give\Donations\Models\Donation;
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
        'donation_id' => 'int',
        'created_at' => DateTime::class,
        'updated_at' => DateTime::class,
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
     * @return ModelQueryBuilder<Event>
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
        return give(EventRepository::class)->queryById($this->event_id);
    }

    /**
     * @unreleased
     *
     * @return ModelQueryBuilder<EventTicketType>
     */
    public function ticketType(): ModelQueryBuilder
    {
        return give(EventTicketTypeRepository::class)->queryById($this->ticket_type_id);
    }


    /**
     * @unreleased
     *
     * @return ModelQueryBuilder<Donation>
     */
    public function donation(): ModelQueryBuilder
    {
        return give()->donations->queryById($this->ticket_type_id);
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
            'donation_id' => (int)$object->donation_id,
            'created_at' => Temporal::toDateTime($object->created_at),
            'updated_at' => Temporal::toDateTime($object->updated_at),
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
