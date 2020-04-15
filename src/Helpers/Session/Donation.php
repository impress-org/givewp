<?php
namespace Give\Helper\Session\Donation;

use function Give\Helper\Session\getSession;
use function give_get_donation_id_by_key as getDonationIdByPurchaseKey;

/**
 * Get donation is from donor session.
 *
 * @return int
 * @since 2.7.0
 */
function getId() {
	$session = getSession();

	return ! empty( $session['purchase_key'] ) ? getDonationIdByPurchaseKey( $session['purchase_key'] ) : 0;
}
