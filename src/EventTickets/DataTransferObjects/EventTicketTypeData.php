<?php

namespace Give\EventTickets\DataTransferObjects;

use Give\EventTickets\Models\EventTicketType;

final class EventTicketTypeData
{

    /**
     * @unreleased
     * @var int
     */
    public $id;

    /**
     * @unreleased
     * @var int
     */
    public $eventId;

    /**
     * @unreleased
     * @var string
     */
    public $title;

    /**
     * @unreleased
     * @var string
     */
    public $description;

    /**
     * @unreleased
     * @var int
     */
    public $price;

    /**
     * @unreleased
     * @var int
     */
    public $capacity;

    /**
     * @unreleased
     * @var int
     */
    public $salesCount;

    /**
     * @unreleased
     * @var int
     */
    public $ticketsAvailable;

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
     * @unreleased
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
