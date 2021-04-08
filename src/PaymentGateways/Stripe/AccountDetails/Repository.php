<?php

namespace Give\PaymentGateways\Stripe\AccountDetails;

/**
 * Class AccountDetail
 *
 * @package Give\PaymentGateways\Stripe
 * @unreleased
 */
class Repository {

	/**
	 * Get account detail by Stripe account id.
	 *
	 * @unreleased
	 * @param string $accountId
	 *
	 * @return array
	 */
	public function getAccountDetail( $accountId ) {
		$accountDetail = array_filter(
			give_stripe_get_all_accounts(),
			static function ( $data ) use ( $accountId ) {
				return $data['account_id'] === $accountId;
			}
		);

		return $accountDetail ? current( $accountDetail ) : $accountDetail;
	}
}
