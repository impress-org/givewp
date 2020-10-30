<?php

namespace Give\TestData\Framework\Provider;

/**
 * Returns a random Donor ID from the donors table.
 */
class RandomDonor extends RandomProvider {

	public function __invoke() {
		global $wpdb;
		$donorIDs = $wpdb->get_col( "SELECT id FROM {$wpdb->prefix}give_donors" );
		return $this->faker->randomElement( $donorIDs );
	}
}
