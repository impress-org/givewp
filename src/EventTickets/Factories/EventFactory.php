<?php

namespace Give\EventTickets\Factories;

use Give\DonationForms\Models\DonationForm;
use Give\Framework\Models\Factories\ModelFactory;

class EventFactory extends ModelFactory
{
    /**
     * @unreleased
     */
    public function definition(): array
    {
        $title = $this->faker->words(3, true);
        $description = $this->faker->paragraph();
        $start_datetime = $this->faker->dateTimeThisYear('+6 months');

        return [
            'id' => wp_insert_post([
                'post_type' => 'give_event',
                'post_title' => $title,
                'post_content' => '[]', // Empty block editor content for potential event page
                'post_excerpt' => $description,
                'post_status' => 'publish',
            ]),
            'title' => $title,
            'description' => $description,
            'start_datetime' => $start_datetime,
            'end_datetime' => $start_datetime->modify('+1 hour'),
            'ticket_close_datetime' => $start_datetime->modify('-1 day'),
        ];
    }
}
