<?php
namespace Give\Revenue;

use Give\Revenue\Repositories\Revenue;

/**
 * Class OnDonationHandler
 * @package Give\Revenue
 * @since 2.9.0
 *
 * use this class to insert revenue when new donation create.
 */
class OnDonationHandler {
	/**
	 * Handle new donation.
	 *
	 * @since 2.9.0
	 *
	 * @param int $donationId
	 * @param array $donationData
	 */
	public function handle( $donationId, $donationData ) {
		/* @var Revenue $revenue */
		$revenue              = give( Revenue::class );
		$donationAmountInCent = give_maybe_sanitize_amount( $donationData['price'], [ 'currency' => $donationData['currency'] ] ) * 100;
		$formId               = (int) $donationData['give_form_id'];

		$revenueData = [
			'donation_id' => $donationId,
			'form_id'     => $formId,
			'amount'      => $donationAmountInCent,
		];

		$revenue->insert( $revenueData );
	}
}
