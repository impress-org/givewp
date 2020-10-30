<?php

namespace Give\TestData;

use WP_CLI;

/**
 * A WP-CLI command for seeding test data.
 */
class DonorSeedCommand {

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
		$count   = WP_CLI\Utils\get_flag_value( $assocArgs, 'count', $default = 10 );
		$preview = WP_CLI\Utils\get_flag_value( $assocArgs, 'preview', $default = false );

		$donors = $this->donorFactory->make( $count );

		if ( $preview ) {
			WP_CLI\Utils\format_items(
				'table',
				$donors,
				array_keys( $this->donorFactory->definition() )
			);
		} else {
			global $wpdb;
			$progress = \WP_CLI\Utils\make_progress_bar( 'Generating donors', $count );
			foreach ( $donors as $donor ) {
				$wpdb->insert(
					"{$wpdb->prefix}give_donors",
					[
						'email' => $donor['email'],
						'name'  => sprintf( '%s %s', $donor['first_name'], $donor['last_name'] ),
					]
				);
				$donorID        = $wpdb->insert_id;
				$metaRepository = new Framework\MetaRepository( 'give_donormeta', 'donor_id' );
				$metaRepository->persist(
					$donorID,
					[
						'_give_donor_first_name' => $donor['first_name'],
						'_give_donor_last_name'  => $donor['last_name'],
					]
				);
				$progress->tick();
			}
			$progress->finish();
		}
	}
}
