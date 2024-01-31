<?php

namespace Give\EventTickets\Factories;

use Give\Framework\Models\Factories\ModelFactory;
use Give\EventTickets\Models\Event;
use Give\EventTickets\Models\EventTicketType;

class EventTicketFactory extends ModelFactory
{
    /**
     * @unreleased
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory()->create()->id,
            'ticket_type_id' => EventTicketType::factory()->create()->id,
        ];
    }
}
