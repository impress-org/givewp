<?php
namespace Give\Donation;

use function Give\Helpers\Form\Template\Utils\Frontend\getPaymentId;

/**
 * Class Donation
 *
 * @package Give\Donation
 */
class Donation {
	private $id;

	/**
	 * Donation constructor.
	 *
	 * @param int|null $id
	 */
	public function __construct( $id = null ) {
		$this->id = $id ?: getPaymentId();
	}


	/**
	 * Return true if donation is subscription.
	 *
	 * @since 2.7.0
	 * @return bool
	 */
	public function isRecurring() {
		return '1' === Give()->payment_meta->get_meta( $this->id, '_give_is_donation_recurring', true );
	}

	/**
	 * Get total donation amount
	 *
	 * @return string
	 */
	public function getTotalDonationAmount() {
		return give_get_meta( $this->id, '_give_payment_total', true );
	}

	/**
	 * Get donation fee
	 *
	 * @return string
	 */
	public function getDonationAmount() {
		return give_get_meta( $this->id, '_give_fee_donation_amount', true );
	}

	/**
	 * Get fee amount.
	 *
	 * @return bool|mixed
	 */
	public function getFeeAmount() {
		return give_get_meta( $this->id, '_give_fee_amount', true );
	}
}
