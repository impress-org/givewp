<?php
namespace Give\Tracking\Helpers;

use Give\Helpers\ArrayDataSet;

/**
 * Class DonationStatuses
 *
 * @package Give\Tracking\Helpers
 * @since 2.10.2
 */
class DonationStatuses {
	/**
	 * Get list of statuses of completed donations.
	 *
	 * @since 2.10.2
	 * @param  false  $asStringSeparatedBySingleQuotes
	 *
	 * @return string|string[]
	 */
	public static function getCompletedDonationsStatues( $asStringSeparatedBySingleQuotes = false ) {
		$statuses = [
			'publish', // One time donation
			'give_subscription', // Renewal
		];

		if ( $asStringSeparatedBySingleQuotes ) {
			$statuses = ArrayDataSet::getStringSeparatedByCommaEnclosedWithSingleQuote( $statuses );
		}

		return $statuses;
	}
}
