<?php

namespace Give\EventTickets\Factories;

use DateTime;
use Give\Framework\Models\Factories\ModelFactory;

class EventFactory extends ModelFactory
{
    /**
     * @since 3.6.0
     */
    public function definition(): array
    {
        $startDateTime = $this->faker->dateTimeThisYear('+6 months');

        return [
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'startDateTime' => $startDateTime,
            'endDateTime' => $startDateTime->modify('+1 hour'),
            'ticketCloseDateTime' => null,
            'createdAt' => new DateTime(),
            'updatedAt' => new DateTime(),
        ];
    }
}
