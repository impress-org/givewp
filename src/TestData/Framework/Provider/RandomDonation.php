<?php

namespace Give\TestData\Framework\Provider;

/**
 * Returns a random Donation ID from the donations table.
 */
class RandomDonation extends RandomProvider
{

    public function __invoke()
    {
        global $wpdb;
        $donations = $wpdb->get_col(
            "SELECT id FROM {$wpdb->posts} WHERE post_type = 'give_payment' AND post_status = 'publish'",
            ARRAY_A
        );

        return $this->faker->randomElement($donations);
    }
}
