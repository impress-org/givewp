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
}
