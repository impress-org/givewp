<?php
namespace Give\Revenue;

use Give\Revenue\Repositories\Revenue;
use Give\ValueObjects\Money;
use WP_Post;

/**
 * Class OnDonationHandler
 * @package Give\Revenue
 * @since 2.9.0
 *
 * use this class to insert revenue when new donation create.
 */
class DonationHandler {
	/**
	 * Handle new donation.
	 *
	 * @param  int  $donationId
	 *
	 * @since 2.9.0
	 *
	 */
	public function handle( $donationId ) {
		/* @var Revenue $revenue */
		$revenue = give( Revenue::class );

		$revenue->insert( $this->getData( $donationId ) );
	}

	/**
	 * Get revenue data.
	 *
	 * @since 2.9.0
	 * @param int $donationId
	 *
	 * @return array
	 */
	public function getData( $donationId ) {
		/* @var Revenue $revenue */
		$donationAmountInCent = Money::of( give_donation_amount( $donationId ), give_get_payment_currency_code( $donationId ) )->getMinorAmount();
		$formId               = give_get_payment_form_id( $donationId );

		return [
			'donation_id' => $donationId,
			'form_id'     => $formId,
			'amount'      => $donationAmountInCent,
		];
	}
}
