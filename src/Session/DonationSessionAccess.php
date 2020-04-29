<?php
namespace Give\Session;

use function give_get_donation_id_by_key as getDonationIdByPurchaseKey;

class DonationSessionAccess extends SessionAccess {
	/**
	 * Session key
	 *
	 * @since 2.7.0
	 * @var string
	 */
	private $id = 'give_purchase';

	/**
	 * Session dat.
	 *
	 * @since 2.7.0a
	 * @var array
	 */
	private $data;

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
