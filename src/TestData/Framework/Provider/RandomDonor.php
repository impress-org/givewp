<?php

namespace Give\TestData\Framework\Provider;

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- WP-CLI test data code; it writes and reads fake data directly for speed, runs only from the command line, and is not cached.

/**
 * Returns a random Donor ID from the donors table.
 */
class RandomDonor extends RandomProvider
{

    public function __invoke()
    {
        global $wpdb;
        $donors = $wpdb->get_results("SELECT id, name, email FROM {$wpdb->prefix}give_donors", ARRAY_A);

        return $this->faker->randomElement($donors);
    }
}
