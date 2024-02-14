<?php

namespace Give\EventTickets\Factories;

use DateTime;
use Give\EventTickets\Models\Event;
use Give\Framework\Models\Factories\ModelFactory;
use Give\Framework\Support\ValueObjects\Money;

class EventTicketTypeFactory extends ModelFactory
{
    /**
     * @unreleased
     */
    public function definition(): array
    {
        return [
            'eventId' => Event::factory()->create()->id,
            'label' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'price' => Money::fromDecimal($this->faker->numberBetween(10, 100), give_get_currency()),
            'totalTickets' => $this->faker->numberBetween(20, 100),
            'createdAt' => new DateTime(),
            'updatedAt' => new DateTime(),
        ];
    }
}
