<?php

namespace Give\PaymentGateways\Stripe;

use Give\PaymentGateways\Stripe\Repositories\AccountDetail;

/**
 * Class DonationProcessing
 *
 * This class use to figure out that whether or not we add application fee for stripe account.
 *
 * @package Give\PaymentGateways\Stripe\Helpers
 * @unreleased
 */
class ProcessingDonation {
	/**
	 * @var Models\AccountDetail
	 */
	protected $accountDetail;

	/**
	 * Returns true or false based on whether the Stripe fee should be applied or not
	 *
	 * @unreleased
	 *
	 * @return bool
	 */
	public static function canAddFee() {
		/* @var $donationProcessing self */
		$donationProcessing = give( __CLASS__ );
		$gate               = new ApplicationFee( $donationProcessing->accountDetail );

		return $gate->canAddFee();
	}

	/**
	 * Set account details.
	 *
	 * @unreleased
	 * @return self
	 */
	public function setAccountDetail() {
		/* @var $accountDetailRepository AccountDetail */
		$accountDetailRepository = give( AccountDetail::class );
		$accountId               = give_stripe_get_connected_account_options()['stripe_account'];

		$this->accountDetail = $accountDetailRepository->getAccountDetail( $accountId );

		return $this;
	}
}
