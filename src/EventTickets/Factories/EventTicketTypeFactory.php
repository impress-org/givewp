<?php

namespace Give\EventTickets\Factories;

use DateTime;
use Give\Framework\Models\Factories\ModelFactory;
use Give\EventTickets\Models\Event;

class EventTicketTypeFactory extends ModelFactory
{
    /**
     * @unreleased
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory()->create()->id,
            'price' => $this->faker->numberBetween(10, 100),
            'max_available' => $this->faker->numberBetween(20, 100),
            'created_at' => new DateTime(),
            'updated_at' => new DateTime(),
        ];
    }
}
