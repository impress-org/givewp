<?php

namespace Give\TestData\Framework\Provider;

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
