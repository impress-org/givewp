<?php

namespace Give\EventTickets\DataTransferObjects;

use Give\EventTickets\Models\Event;
use Give\EventTickets\Models\EventTicketType;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Support\Facades\DateTime\Temporal;
use stdClass;

/**
 * @since 3.6.0
 */
class TicketPurchaseData
{
    /**
     * @since 3.6.0
     * @var int
     */
    protected $quantity;

    /**
     * @since 3.6.0
     * @var EventTicketType
     */
    protected $ticketType;

    /**
     * @since 3.6.0
     */
    public function __get($name)
    {
        return $this->$name;
    }

    /**
     * @since 4.16.8.1 Require the ticket type to belong to the given event and that event to not have already ended, and clamp quantity to a non-negative integer.
     * @since 3.6.0
     *
     * @throws InvalidArgumentException
     */
    public static function fromFieldValueObject(stdClass $object, Event $event): self
    {
        $ticketType = EventTicketType::find($object->ticketId ?? 0);

        if (!$ticketType || $ticketType->eventId !== $event->id) {
            throw new InvalidArgumentException('Ticket type does not belong to this event.');
        }

        if ($event->endDateTime < Temporal::getCurrentDateTime()) {
            throw new InvalidArgumentException('Event has already ended.');
        }

        $self = new self();

        $self->quantity = max(0, (int) ($object->quantity ?? 0));
        $self->ticketType = $ticketType;

        return $self;
    }
}
