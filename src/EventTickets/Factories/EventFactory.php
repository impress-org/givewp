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
        $start_datetime = $this->faker->dateTimeThisYear;

        return [
            'id' => wp_insert_post([
                'post_type' => 'give_event',
                'post_title' => $this->faker->text,
                'post_content' => $this->faker->text,
                'post_status' => 'publish',
            ]),
            'form_id' => DonationForm::factory()->create()->id,
            'description' => $this->faker->text,
            'start_datetime' => $start_datetime,
            'end_datetime' => $start_datetime->modify('+1 hour'),
            'ticket_close_datetime' => $start_datetime->modify('-1 day'),
        ];
    }
}
