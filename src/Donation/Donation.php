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
	 * @param int $id
	 */
	public function __construct( $id ) {
		$this->id = absint( $id );
	}


	/**
	 * Return true if donation is subscription.
	 *
	 * @since 2.7.0
	 * @param int $donationId
	 * @return bool
	 */
	public static function isRecurring( $donationId = null ) {
		$donationId = $donationId ?: getPaymentId();

		return '1' === Give()->payment_meta->get_meta( $donationId, '_give_is_donation_recurring', true );
	}
}
