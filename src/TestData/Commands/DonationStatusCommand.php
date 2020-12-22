<?php

namespace Give\TestData\Commands;

use WP_CLI;

/**
 * Class DonationStatusCommand
 * @package Give\TestData\Commands
 *
 * A WP-CLI command to get all donation statuses available
 */
class DonationStatusCommand {
	/**
	 * Get available donation statuses
	 *
	 * ## EXAMPLES
	 *
	 *     wp give test-donation-statuses
	 *
	 * @when after_wp_load
	 */
	public function __invoke( $args, $assocArgs ) {
		// Get donation statuses
		$statuses = give_get_payment_statuses();

		$formatted = [];

		foreach ( $statuses as $status => $name ) {
			$formatted[] = [
				'Name'   => $name,
				'Status' => $status,
			];
		}

		WP_CLI\Utils\format_items(
			'table',
			$formatted,
			[ 'Name', 'Status' ]
		);
	}
}
