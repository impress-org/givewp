<?php

namespace Give\TestData\Addons\FeeRecovery;

use Throwable;
use Give\TestData\Framework\MetaRepository;

class FeeRecovery {
	/**
	 * @param  int  $donationID
	 * @param  array  $donation
	 * @param  array  $params
	 */
	public function addFee( $donationID, $donation, $params ) {
		global $wpdb;

		// Fee recovery is checked?
		if (
			! isset( $params['donation_cover_fees'] )
			|| ! filter_var( $params['donation_cover_fees'], FILTER_VALIDATE_BOOLEAN )
		) {
			return;
		}

		// Start DB transaction
		$wpdb->query( 'START TRANSACTION' );

		try {
			// Update donation meta
			$metaRepository = new MetaRepository( 'give_donationmeta', 'donation_id' );
			$metaRepository->persist(
				$donationID,
				[
					'_give_fee_donation_amount' => $donation['payment_total'],
					'_give_fee_amount'          => give_get_option( 'give_fee_percentage', 2.90 ),
				]
			);

			$wpdb->query( 'COMMIT' );

		} catch ( Throwable $e ) {
			$wpdb->query( 'ROLLBACK' );
		}
	}

}
