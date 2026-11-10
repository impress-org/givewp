<?php

namespace Give\TestData\Framework\Provider;

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- WP-CLI test data code; it writes and reads fake data directly for speed, runs only from the command line, and is not cached.

/**
 * Returns a random Donor ID from the donors table.
 */
class RandomDonationForm extends RandomProvider
{

    public function __invoke()
    {
        global $wpdb;
        $donationForms = $wpdb->get_results(
            "SELECT id, post_title FROM {$wpdb->posts} WHERE post_type = 'give_forms' AND post_status = 'publish'",
            ARRAY_A
        );

        return $this->faker->randomElement($donationForms);
    }
}
