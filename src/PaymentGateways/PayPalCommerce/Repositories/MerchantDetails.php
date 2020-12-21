<?php

namespace Give\PaymentGateways\PayPalCommerce\Repositories;

use Give\Helpers\ArrayDataSet;
use Give\PaymentGateways\PayPalCommerce\Models\MerchantDetail;
use Give\PaymentGateways\PayPalCommerce\PayPalClient;
use Give\PaymentGateways\PayPalCommerce\Repositories\Traits\HasMode;

/**
 * Class MerchantDetails
 *
 * @since 2.9.0
 */
class MerchantDetails {
	use HasMode;

	/**
	 * Returns whether or not the account has been connected
	 *
	 * @since 2.9.0
	 * @since 2.9.6 Check for PayPal merchant id to confirm whether or not account is connected.
	 *
	 * @return bool
	 */
	public function accountIsConnected() {
		/* @var $merchantDetails MerchantDetail */
		$merchantDetails = give( MerchantDetail::class );

		return (bool) $merchantDetails->merchantIdInPayPal;
	}

	/**
	 * Get merchant details.
	 *
	 * @since 2.9.0
	 *
	 * @return MerchantDetail
	 */
	public function getDetails() {
		return MerchantDetail::fromArray( get_option( $this->getAccountKey(), [] ) );
	}

	/**
	 * Save merchant details.
	 *
	 * @since 2.9.0
	 *
	 * @param MerchantDetail $merchantDetails
	 *
	 * @return bool
	 */
	public function save( MerchantDetail $merchantDetails ) {
		return update_option( $this->getAccountKey(), $merchantDetails->toArray() );
	}

	/**
	 * Delete merchant details.
	 *
	 * @since 2.9.0
	 *
	 * @return bool
	 */
	public function delete() {
		return delete_option( $this->getAccountKey() );
	}

	/**
	 * Returns the account errors if there are any
	 *
	 * @since 2.9.0
	 *
	 * @return string[]|null
	 */
	public function getAccountErrors() {
		return get_option( $this->getAccountErrorsKey(), null );
	}

	/**
	 * Saves the account error message
	 *
	 * @since 2.9.0
	 *
	 * @param string[] $errorMessage
	 *
	 * @return bool
	 */
	public function saveAccountErrors( $errorMessage ) {
		return update_option( $this->getAccountErrorsKey(), $errorMessage );
	}

	/**
	 * Deletes the errors for the account
	 *
	 * @since 2.9.0
	 *
	 * @return bool
	 */
	public function deleteAccountErrors() {
		return delete_option( $this->getAccountErrorsKey() );
	}

	/**
	 * Deletes the client token for the account
	 *
	 * @since 2.9.0
	 *
	 * @return bool
	 */
	public function deleteClientToken() {
		return delete_transient( $this->getClientTokenKey() );
	}

	/**
	 * Get client token for hosted credit card fields.
	 *
	 * @since 2.9.0
	 *
	 * @return string
	 */
	public function getClientToken() {
		$optionName = $this->getClientTokenKey();

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

	/**
	 * Returns the options key for the account in the give mode
	 *
	 * @since 2.9.0
	 *
	 * @return string
	 */
	public function getAccountKey() {
		return "give_paypal_commerce_{$this->mode}_account";
	}

	/**
	 * Returns the options key for the account errors in the give mode
	 *
	 * @since 2.9.0
	 *
	 * @return string
	 */
	private function getAccountErrorsKey() {
		return "give_paypal_commerce_{$this->mode}_account_errors";
	}

	/**
	 * Returns the options key for the client token in the give mode
	 *
	 * @since 2.9.0
	 *
	 * @return string
	 */
	private function getClientTokenKey() {
		return "give_paypal_commerce_{$this->mode}_client_token";
	}
}
