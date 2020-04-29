<?php
namespace Give\Session\Access;

use function give_get_donation_id_by_key as getDonationIdByPurchaseKey;

class Donation extends Access {
	/**
	 * Session Id
	 *
	 * @since 2.7.0
	 * @var string
	 */
	protected $sessionKey = 'give_purchase';

	/**
	 * gEt donation id.
	 *
	 * @since 2.7.0
	 * @return int
	 */
	public function getDonationId() {
		return ! empty( $this->data['purchase_key'] ) ?
			absint( getDonationIdByPurchaseKey( $this->data['purchase_key'] ) ) :
			0;
	}
}
