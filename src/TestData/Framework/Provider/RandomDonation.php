<?php

namespace Give\TestData\Framework\Provider;

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- WP-CLI test data code; it writes and reads fake data directly for speed, runs only from the command line, and is not cached.

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
