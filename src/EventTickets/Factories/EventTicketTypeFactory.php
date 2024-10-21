<?php

namespace Give\EventTickets\Factories;

use DateTime;
use Give\EventTickets\Models\Event;
use Give\Framework\Models\Factories\ModelFactory;
use Give\Framework\Support\ValueObjects\Money;

class EventTicketTypeFactory extends ModelFactory
{
    /**
     * @since 3.6.0
     */
    public function definition(): array
    {
        return [
            'eventId' => Event::factory()->create()->id,
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'price' => new Money($this->faker->numberBetween(1000, 10000), give_get_currency()),
            'capacity' => $this->faker->numberBetween(20, 100),
            'createdAt' => new DateTime(),
            'updatedAt' => new DateTime(),
        ];
    }
}
