<?php

namespace Give\EventTickets\Models;

use DateTime;
use Give\DonationForms\Models\DonationForm;
use Give\EventTickets\Factories\EventFactory;
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
 * @since 3.6.0
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
        'startDateTime' => DateTime::class,
        'endDateTime' => DateTime::class,
        'ticketCloseDateTime' => DateTime::class,
        'createdAt' => DateTime::class,
        'updatedAt' => DateTime::class,
    ];

    /**
     * @inheritdoc
     */
    protected $relationships = [
        'tickets' => Relationship::HAS_MANY,
        'ticketTypes' => Relationship::HAS_MANY,
        'forms' => Relationship::BELONGS_TO_MANY,
    ];

    /**
     * @since 3.6.0
     *
     * @return Event|null
     */
    public static function find($id)
    {
        return give(EventRepository::class)->getById($id);
    }


    /**
     * @since 3.6.0
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
     * @since 3.6.0
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
     * @since 3.6.0
     *
     * @throws Exception|InvalidArgumentException
     */
    public function delete(): bool
    {
        return give(EventRepository::class)->delete($this);
    }

    /**
     * @since 3.6.0
     *
     * @return ModelQueryBuilder<Event>
     */
    public static function query(): ModelQueryBuilder
    {
        return give(EventRepository::class)->prepareQuery();
    }

    /**
     * @since 3.6.0
     *
     * @return ModelQueryBuilder<EventTicketType>
     */
    public function ticketTypes(): ModelQueryBuilder
    {
        return give(EventTicketTypeRepository::class)->queryByEventId($this->id);
    }

    /**
     * @since 3.6.0
     *
     * @return ModelQueryBuilder<EventTicket>
     */
    public function eventTickets(): ModelQueryBuilder
    {
        return give(EventTicketRepository::class)->queryByEventId($this->id);
    }

    public function forms(): ModelQueryBuilder
    {
        $eventIdPattern = sprintf('"eventId":%s', $this->id);

        return DonationForm::query()
            ->whereLike('give_formmeta_attach_meta_fields.meta_value', '%"name":"givewp/event-tickets"%')
            ->where(function($query) use ($eventIdPattern) {
                $query->whereLike('give_formmeta_attach_meta_fields.meta_value', "%$eventIdPattern}%") // When the eventId is the only block attribute.
                ->orWhereLike('give_formmeta_attach_meta_fields.meta_value', "%$eventIdPattern,%"); // When the eventId is the NOT only block attribute.
            });
    }

    /**
     * @since 3.6.0
     *
     * @param object $object
     */
    public static function fromQueryBuilderObject($object): Event
    {
        return new Event([
            'id' => (int)$object->id,
            'title' => (string)$object->title,
            'description' => (string)$object->description,
            'startDateTime' => $object->start_datetime ? Temporal::toDateTime($object->start_datetime) : null,
            'endDateTime' => $object->end_datetime ? Temporal::toDateTime($object->end_datetime) : null,
            'ticketCloseDateTime' => $object->ticket_close_datetime ? Temporal::toDateTime(
                $object->ticket_close_datetime
            ) : null,
            'createdAt' => Temporal::toDateTime($object->created_at),
            'updatedAt' => Temporal::toDateTime($object->updated_at),
        ]);
    }

    /**
     * @since 3.6.0
     */
    public static function factory(): EventFactory
    {
        return new EventFactory(static::class);
    }
}
