<?php

namespace Give\TestData\Addons\Funds;

use Give\TestData\Framework\Factory;

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- WP-CLI test data code; it writes and reads fake data directly for speed, runs only from the command line, and is not cached.

/**
 * Class FundFactory
 * @package Give\TestData\Funds
 */
class FundFactory extends Factory
{

    /**
     * @return int
     */
    public function getRandomFund()
    {
        global $wpdb;
        $fundIds = $wpdb->get_col("SELECT id FROM {$wpdb->prefix}give_funds");

        return $this->faker->randomElement($fundIds);
    }

    /**
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->catchPhrase(),
            'description' => $this->faker->sentence(6, true),
            'is_default' => 0,
            'author_id' => $this->randomAuthor(),
            'date_created' => $this->faker->dateTimeThisYear()->format('Y-m-d H:i:s'),
            'date_modified' => $this->faker->dateTimeThisYear()->format('Y-m-d H:i:s'),
        ];
    }
}
