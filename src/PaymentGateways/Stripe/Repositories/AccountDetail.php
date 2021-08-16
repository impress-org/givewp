<?php

namespace Give\PaymentGateways\Stripe\Repositories;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Exceptions\Primitives\InvalidPropertyName;
use Give\PaymentGateways\Stripe\Models\AccountDetail as AccountDetailModel;

/**
 * Class AccountDetail
 *
 * @package Give\PaymentGateways\Stripe\Repository
 * @since 2.10.2
 */
class AccountDetail {
	/**
	 * Return Stripe account id for donation form.
	 *
	 * @param int $formId
	 *
	 * @return AccountDetailModel
	 * @throws InvalidPropertyName
	 * @since 2.10.2
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
	 * @param string $accountId
	 *
	 * @return AccountDetailModel
	 * @throws InvalidPropertyName
	 * @since 2.10.2
	 */
	public function getAccountDetail( $accountId ) {
		$accountDetail = array_filter(
			give_stripe_get_all_accounts(),
			static function ( $data ) use ( $accountId ) {
				return $data['account_id'] === $accountId;
			}
		);

		$accountDetail = $accountDetail ? current( $accountDetail ) : $accountDetail;
		return new AccountDetailModel( $accountDetail );
	}

	/**
	 * Get account detail by Stripe account slug.
	 *
	 * @unlreased
	 *
	 * @param string $accountSlug
	 *
	 * @return AccountDetailModel
	 * @throws InvalidArgumentException|InvalidPropertyName
	 */
	public function getAccountDetailBySlug( $accountSlug ) {
		$accountDetail = array_filter(
			give_stripe_get_all_accounts(),
			static function ( $data ) use ( $accountSlug ) {
				return $data['account_slug'] === $accountSlug;
			}
		);

		if ( ! $accountDetail ) {
			throw new InvalidArgumentException(
				sprintf(
					'Stripe account with %s account slug does not exist',
					$accountSlug
				)
			);
		}

		$accountDetail = current( $accountDetail );
		return new AccountDetailModel( $accountDetail );
	}

	/**
	 * @unreleased
	 */
	public function getAllStripeAccounts() {
		return give_stripe_get_all_accounts();
	}

	/**
	 * @unreleased
	 * @return string
	 */
	public function getDefaultStripeAccountSlug() {
		return give_stripe_get_default_account_slug();
	}

	/**
	 * @unreleased
	 *
	 * @param int $formId
	 *
	 * @return bool|mixed|string
	 */
	public function getDefaultStripeAccountSlugForDonationForm( $formId ) {
		return give()->form_meta->get_meta( $formId, '_give_stripe_default_account', true );
	}

	/**
	 * @unreleased
	 *
	 * @param int $formId
	 *
	 * @return bool
	 */
	public function setDefaultStripeAccountSlugForDonationForm( $formId, $stripeAccountSlug ) {
		return give()->form_meta->update_meta( $formId, '_give_stripe_default_account', $stripeAccountSlug );
	}
}
