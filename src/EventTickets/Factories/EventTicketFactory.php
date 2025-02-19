<?php

namespace Give\EventTickets\Factories;

use DateTime;
use Exception;
use Give\Donations\Models\Donation;
use Give\EventTickets\Models\Event;
use Give\EventTickets\Models\EventTicketType;
use Give\Framework\Models\Factories\ModelFactory;

class EventTicketFactory extends ModelFactory
{
    /**
     * @since 3.20.0 Add amount to the properties array using the price from the ticket type
     * @since 3.6.0
     *
     * @throws Exception
     */
    public function definition(): array
    {
        $ticketType = EventTicketType::factory()->create();

        return [
            'eventId' => Event::factory()->create()->id,
            'ticketTypeId' => $ticketType->id,
            'donationId' => Donation::factory()->create()->id,
            'amount' => $ticketType->price,
            'createdAt' => new DateTime(),
            'updatedAt' => new DateTime(),
        ];
    }
}
