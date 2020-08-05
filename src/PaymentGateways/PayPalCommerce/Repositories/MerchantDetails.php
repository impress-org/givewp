<?php

namespace Give\PaymentGateways\PayPalCommerce\Repositories;

use Give\Helpers\ArrayDataSet;
use Give\PaymentGateways\PayPalCommerce\Models\MerchantDetail;
use Give\PaymentGateways\PayPalCommerce\OptionId;
use Give\PaymentGateways\PayPalCommerce\PayPalClient;

/**
 * Class MerchantDetails
 *
 * @since 2.8.0
 */
class MerchantDetails {
	const ACCOUNT_OPTION_KEY = 'give_paypal_commerce_account';

	const ERRORS_OPTION_KEY = 'give_paypal_commerce_account_errors';

	/**
	 * Get merchant details.
	 *
	 * @since 2.8.0
	 *
	 * @return MerchantDetail
	 */
	public function getDetails() {
		return MerchantDetail::fromArray( get_option( self::ACCOUNT_OPTION_KEY, [] ) );
	}

	/**
	 * Save merchant details.
	 *
	 * @since 2.8.0
	 *
	 * @param MerchantDetail $merchantDetails
	 *
	 * @return bool
	 */
	public function save( MerchantDetail $merchantDetails ) {
		return update_option( self::ACCOUNT_OPTION_KEY, $merchantDetails->toArray() );
	}

	/**
	 * Delete merchant details.
	 *
	 * @since 2.8.0
	 *
	 * @return bool
	 */
	public function delete() {
		return delete_option( self::ACCOUNT_OPTION_KEY );
	}

	/**
	 * Returns the account errors if there are any
	 *
	 * @since 2.8.0
	 *
	 * @return string[]|null
	 */
	public function getAccountErrors() {
		return json_decode( get_option( self::ERRORS_OPTION_KEY, null ), true );
	}

	/**
	 * Saves the account error message
	 *
	 * @since 2.8.0
	 *
	 * @param string[] $errorMessage
	 *
	 * @return bool
	 */
	public function saveAccountErrors( $errorMessage ) {
		return update_option( self::ERRORS_OPTION_KEY, json_encode( $errorMessage ) );
	}

	/**
	 * Deletes the errors for the account
	 *
	 * @since 2.8.0
	 *
	 * @return bool
	 */
	public function deleteAccountErrors() {
		return delete_option( self::ERRORS_OPTION_KEY );
	}

	/**
	 * Get client token for hosted credit card fields.
	 *
	 * @since 2.8.0
	 *
	 * @return string
	 */
	public function getClientToken() {
		$optionName = 'give_paypal_commerce_client_token';

		if ( $optionValue = get_transient( $optionName ) ) {
			return $optionValue;
		}

		/** @var MerchantDetail $merchant */
		$merchant = give( MerchantDetail::class );

		$response = wp_remote_retrieve_body(
			wp_remote_post(
				give( PayPalClient::class )->getApiUrl( 'v1/identity/generate-token' ),
				[
					'headers' => [
						'Accept'          => 'application/json',
						'Accept-Language' => 'en_US',
						'Authorization'   => sprintf(
							'Bearer %1$s',
							$merchant->accessToken
						),
						'Content-Type'    => 'application/json',
					],
				]
			)
		);

		if ( ! $response ) {
			return '';
		}

		$response = ArrayDataSet::camelCaseKeys( json_decode( $response, true ) );

		if ( ! array_key_exists( 'clientToken', $response ) ) {
			return '';
		}

		set_transient(
			$optionName,
			$response['clientToken'],
			$response['expiresIn'] - 60 // Expire token before one minute to prevent unnecessary race condition.
		);

		return $response['clientToken'];
	}
}
