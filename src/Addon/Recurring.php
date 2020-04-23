<?php

namespace Give\Addon;

use Give_Subscription;
use Give_Subscriptions_DB;
use function Give\Helpers\Form\Template\Utils\Frontend\getPaymentId;

/**
 * Class Addon
 *
 * @package Give\Addon
 */
class Recurring implements Addonable {

	/**
	 * @inheritDoc
	 */
	public static function isActive() {
		return defined( 'GIVE_RECURRING_VERSION' );
	}

	/**
	 * Get subscription from donation id.
	 *
	 * @since 2.7.0
	 *
	 * @param int|null $donationID
	 *
	 * @return Give_Subscription|null
	 */
	public static function getSubscriptionFromInitialDonationId( $donationID ) {
		$donationID     = $donationID ?: getPaymentId();
		$subscriptionDB = new Give_Subscriptions_DB();
		$subscriptionId = $subscriptionDB->get_column_by( 'id', 'parent_payment_id', $donationID );

		return $subscriptionId ? new Give_Subscription( $subscriptionId ) : null;
	}
}
