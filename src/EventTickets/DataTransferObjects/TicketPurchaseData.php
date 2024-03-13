<?php

namespace Give\EventTickets\DataTransferObjects;

use Give\EventTickets\Models\EventTicketType;
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
     * @since 3.6.0
     */
    public static function fromFieldValueObject(stdClass $object): self
    {
        $self = new self();

        $self->quantity = $object->quantity;
        $self->ticketType = EventTicketType::find($object->ticketId);

        return $self;
    }
}
