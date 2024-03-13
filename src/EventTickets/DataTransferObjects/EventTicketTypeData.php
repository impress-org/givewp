<?php

namespace Give\EventTickets\DataTransferObjects;

use Give\EventTickets\Models\EventTicketType;

final class EventTicketTypeData
{

    /**
     * @since 3.6.0
     * @var int
     */
    public $id;

    /**
     * @since 3.6.0
     * @var int
     */
    public $eventId;

    /**
     * @since 3.6.0
     * @var string
     */
    public $title;

    /**
     * @since 3.6.0
     * @var string
     */
    public $description;

    /**
     * @since 3.6.0
     * @var int
     */
    public $price;

    /**
     * @since 3.6.0
     * @var int
     */
    public $capacity;

    /**
     * @since 3.6.0
     * @var int
     */
    public $salesCount;

    /**
     * @since 3.6.0
     * @var int
     */
    public $ticketsAvailable;

    /**
     * @since 3.6.0
     */
    public static function make(EventTicketType $ticketType)
    {
        $self = new self();

        $self->id = $ticketType->id;
        $self->eventId = $ticketType->eventId;
        $self->title = $ticketType->title;
        $self->description = $ticketType->description;
        $self->price = $ticketType->price->formatToMinorAmount();
        $self->capacity = $ticketType->capacity;
        $self->salesCount = $ticketType->eventTickets()->count();
        $self->ticketsAvailable = $self->capacity - $self->salesCount;

        return $self;
    }

    /**
     * @since 3.6.0
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
