<?php

namespace Give\EventTickets\Factories;

use DateTime;
use Give\DonationForms\Models\DonationForm;
use Give\Framework\Models\Factories\ModelFactory;

class EventFactory extends ModelFactory
{
    /**
     * @unreleased
     */
    public function definition(): array
    {
        $start_datetime = $this->faker->dateTimeThisYear('+6 months');

        return [
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'start_datetime' => $start_datetime,
            'end_datetime' => $start_datetime->modify('+1 hour'),
            'ticket_close_datetime' => $start_datetime->modify('-1 day'),
            'created_at' => new DateTime(),
            'updated_at' => new DateTime(),
        ];
    }
}
