<?php

namespace Give\EventTickets\Factories;

use Give\Framework\Models\Factories\ModelFactory;
use GiveEvents\Events\Models\Event;

class EventTicketTypeFactory extends ModelFactory
{
    /**
     * @unreleased
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory()->create()->id,
        ];
    }
}
