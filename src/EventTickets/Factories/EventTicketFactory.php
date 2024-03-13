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
     * @since 3.6.0
     * @throws Exception
     */
    public function definition(): array
    {
        return [
            'eventId' => Event::factory()->create()->id,
            'ticketTypeId' => EventTicketType::factory()->create()->id,
            'donationId' => Donation::factory()->create()->id,
            'createdAt' => new DateTime(),
            'updatedAt' => new DateTime(),
        ];
    }
}
