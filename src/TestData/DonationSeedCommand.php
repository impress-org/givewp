<?php

namespace Give\TestData;

use WP_CLI;

/**
 * A WP-CLI command for seeding test data.
 */
class DonationSeedCommand {

	/**
	 * @param DonationFactory $factory
	 */
	public function __construct( DonationFactory $donationFactory, DonorFactory $donorFactory ) {
		$this->donationFactory = $donationFactory;
		$this->donorFactory    = $donorFactory;
	}

	/**
	 * @param $args
	 * @param array $assocArgs
	 */
	public function __invoke( $args, $assocArgs ) {
		global $wpdb;
		$count   = WP_CLI\Utils\get_flag_value( $assocArgs, 'count', $default = 10 );
		$preview = WP_CLI\Utils\get_flag_value( $assocArgs, 'preview', $default = false );

		$donations = $this->donationFactory->make( $count );

		if ( $preview ) {
			WP_CLI\Utils\format_items(
				'table',
				$donations,
				array_keys( $this->donationFactory->definition() )
			);
		} else {
			$progress = \WP_CLI\Utils\make_progress_bar( 'Generating donations', $count );
			foreach ( $donations as $donation ) {
				$wpdb->insert(
					"{$wpdb->prefix}posts",
					[
						'post_type' => 'give_payment',
						'post_date' => $donation['completed_date'],
					]
				);
				$donationID     = $wpdb->insert_id;
				$metaRepository = new Framework\MetaRepository( 'give_donationmeta', 'donation_id' );
				$metaRepository->persist(
					$donationID,
					[
						'_give_payment_donor_id'     => $donation['donor_id'],
						'_give_payment_total'        => $donation['payment_total'],
						'_give_payment_currency'     => $donation['payment_currency'],
						'_give_payment_gateway'      => $donation['payment_gateway'],
						'_give_payment_mode'         => $donation['payment_mode'],
						'_give_payment_purchase_key' => $donation['payment_purchase_key'],
						'_give_completed_date'       => $donation['completed_date'],
					]
				);
				$progress->tick();
			}
			$progress->finish();
		}
	}
}
