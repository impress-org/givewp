<?php

namespace Give\EventTickets\Factories;

use DateTime;
use Give\Framework\Models\Factories\ModelFactory;
use Give\EventTickets\Models\Event;
use Give\Framework\Support\ValueObjects\Money;

class EventTicketTypeFactory extends ModelFactory
{
    /**
     * @unreleased
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory()->create()->id,
            'label' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'price' => Money::fromDecimal($this->faker->numberBetween(10, 100), give_get_currency()),
            'max_available' => $this->faker->numberBetween(20, 100),
            'created_at' => new DateTime(),
            'updated_at' => new DateTime(),
        ];
    }
}
