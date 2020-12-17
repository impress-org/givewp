<?php

namespace Give\TestData\Framework\Provider;

/**
 * Returns a random Donation status.
 */
class RandomDonationStatus extends RandomProvider {

	public function __invoke() {
		$statuses = array_keys( give_get_payment_statuses() );

		return $this->faker->randomElement( $statuses );
	}
}
