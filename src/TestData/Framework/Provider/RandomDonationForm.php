<?php

namespace Give\TestData\Framework\Provider;

/**
 * Returns a random Donor ID from the donors table.
 */
class RandomDonationForm extends RandomProvider {

	public function __invoke() {
		global $wpdb;
		$donationForms = $wpdb->get_results( "SELECT id, post_title FROM {$wpdb->posts} WHERE post_type = 'give_forms' AND post_status = 'publish'", ARRAY_A );

		return $this->faker->randomElement( $donationForms );
	}
}
