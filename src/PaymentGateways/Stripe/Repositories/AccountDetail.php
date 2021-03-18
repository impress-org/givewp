<?php

namespace Give\PaymentGateways\Stripe\Repositories;

/**
 * Class AccountDetail
 *
 * @package Give\PaymentGateways\Stripe\Repository
 * @unreleased
 */
class AccountDetail {
	/**
	 * Return Stripe account id for donation form.
	 *
	 * @unreleased
	 * @param int $formId
	 *
	 * @return \Give\PaymentGateways\Stripe\Models\AccountDetail
	 */
	public function getDonationFormStripeAccountId( $formId ) {
		$formHasStripeAccount = give_is_setting_enabled( give_get_meta( $formId, 'give_stripe_per_form_accounts', true ) );
		if ( $formId > 0 && $formHasStripeAccount ) {
			// Return default Stripe account of the form, if enabled.
			$accountId = give_get_meta( $formId, '_give_stripe_default_account', true );
		} else {
			// Global Stripe account.
			$accountId = give_get_option( '_give_stripe_default_account', '' );
		}

		return $this->getAccountDetail( $accountId );
	}

	/**
	 * Get account detail by Stripe account id.
	 *
	 * @unreleased
	 * @param string $accountId
	 *
	 * @return \Give\PaymentGateways\Stripe\Models\AccountDetail
	 */
	public function getAccountDetail( $accountId ) {
		$accountDetail = array_filter(
			give_stripe_get_all_accounts(),
			static function ( $data ) use ( $accountId ) {
				return $data['account_id'] === $accountId;
			}
		);

		$accountDetail = $accountDetail ? current( $accountDetail ) : $accountDetail;
		return new \Give\PaymentGateways\Stripe\Models\AccountDetail( $accountDetail );
	}
}
