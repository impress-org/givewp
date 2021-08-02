<?php

namespace Give\PaymentGateways\Stripe\Repositories;

use Give\PaymentGateways\Stripe\Models\AccountDetail as AccountDetailModel;
use GiveStripe\Infrastructure\Exceptions\DuplicateStripeAccountName;
use GiveStripe\Infrastructure\Exceptions\StripeAccountAlreadyConnected;
use function esc_html__;
use function give_get_option;
use function give_stripe_get_all_accounts;
use function give_update_option;

/**
 * Class Settings
 * @package GiveStripe\Settings\Repositories
 *
 * @unreleased
 */
class Settings {
	/**
	 * @unreleased
	 * @return bool
	 */
	public function hasDefaultGlobalStripeAccountSlug() {
		return (bool) give_get_option( '_give_stripe_default_account', '' );
	}

	/**
	 * @unreleased
	 *
	 * @param string $accountSlug
	 *
	 * @return bool
	 */
	public function setDefaultGlobalStripeAccountSlug( $accountSlug ) {
		return give_update_option( '_give_stripe_default_account', $accountSlug );
	}

	/**
	 * @unreleased
	 * @throws StripeAccountAlreadyConnected
	 * @throws DuplicateStripeAccountName
	 */
	public function addNewStripeAccount( AccountDetailModel $stripeAccount ) {
		$allAccounts = give_stripe_get_all_accounts();
		$accountSlug = $stripeAccount->accountSlug;

		if ( ! $this->isUniqueAccountName( $stripeAccount->accountName, $allAccounts ) ) {
			throw new DuplicateStripeAccountName( esc_html__( 'Stripe account already exist with same name.', 'give' ) );
		}

		if (
			array_key_exists( $accountSlug, $allAccounts ) ||
			$this->isAccountAlreadyConnected( $stripeAccount, $allAccounts )
		) {
			throw new StripeAccountAlreadyConnected( esc_html__( 'Stripe account already connected', 'give' ) );
		}

		$allAccounts[ $accountSlug ] = $stripeAccount->toArray();

		return give_update_option( '_give_stripe_get_all_accounts', $allAccounts );
	}

	/**
	 * @unreleased
	 */
	public function updateStripeAccount( AccountDetailModel $stripeAccount ) {
		$allAccounts = give_stripe_get_all_accounts();
		$accountSlug = $stripeAccount->accountSlug;

		if ( array_key_exists( $accountSlug, $allAccounts ) ) {
			$accountDetails = $stripeAccount->toArray();

			// account_id, account_slug  are unique value which used to reference to connect stripe account.
			// They can not be renamed.
			unset( $accountDetails['account_id'], $accountDetails['account_slug'] );

			$allAccounts[ $accountSlug ] = $stripeAccount->toArray();

			return give_update_option( '_give_stripe_get_all_accounts', $allAccounts );
		}

		return false;
	}

	/**
	 * @unreleased
	 *
	 * @param AccountDetailModel $stripeAccount
	 * @param array $allAccounts
	 *
	 * @return bool
	 */
	public function isAccountAlreadyConnected( AccountDetailModel $stripeAccount, $allAccounts ) {
		foreach ( $allAccounts as $account ) {
			$savedStripeAccount = AccountDetailModel::fromArray( $account );

			if (
				$savedStripeAccount->liveSecretKey === $stripeAccount->liveSecretKey &&
				$savedStripeAccount->livePublishableKey === $stripeAccount->livePublishableKey &&
				$savedStripeAccount->testSecretKey === $stripeAccount->testSecretKey &&
				$savedStripeAccount->testPublishableKey === $stripeAccount->testPublishableKey
			) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @unreleased
	 *
	 * @param string $stripeAccountName
	 * @param array $allAccounts
	 *
	 * @return bool
	 */
	public function isUniqueAccountName( $stripeAccountName, $allAccounts ) {
		foreach ( $allAccounts as $account ) {
			$savedStripeAccount = AccountDetailModel::fromArray( $account );

			if ( $savedStripeAccount->accountName === $stripeAccountName ) {
				return false;
			}
		}

		return true;
	}
}
