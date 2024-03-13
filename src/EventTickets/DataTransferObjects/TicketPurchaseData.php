<?php

namespace Give\EventTickets\DataTransferObjects;

use Give\EventTickets\Models\EventTicketType;
use stdClass;

/**
 * @unreleased
 */
class TicketPurchaseData
{
    /**
     * @unreleased
     * @var int
     */
    protected $quantity;

    /**
     * @unreleased
     * @var EventTicketType
     */
    protected $ticketType;

    /**
     * @unreleased
     */
    public function __get($name)
    {
        return $this->$name;
    }

    /**
     * @unreleased
     */
    public static function fromFieldValueObject(stdClass $object): self
    {
        $self = new self();

        $self->quantity = $object->quantity;
        $self->ticketType = EventTicketType::find($object->ticketId);

        return $self;
    }
}
