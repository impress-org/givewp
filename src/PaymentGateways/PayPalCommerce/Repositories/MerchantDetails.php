<?php
namespace Give\PaymentGateways\PayPalCommerce\Repositories;

use Give\Helpers\ArrayDataSet;
use Give\PaymentGateways\PayPalCommerce\MerchantDetail;
use Give\PaymentGateways\PayPalCommerce\OptionId;
use Give\PaymentGateways\PayPalCommerce\PayPalClient;

/**
 * Class MerchantDetails
 * @package Give\PaymentGateways\PayPalCommerce\Repositories
 *
 * @since 2.8.0
 */
class MerchantDetails {
	/**
	 * Get merchant details.
	 *
	 * @since 2.8.0
	 *
	 * @return MerchantDetail
	 */
	public static function getDetails() {
		return MerchantDetail::fromArray( get_option( OptionId::$payPalAccountsOptionKey, [] ) );
	}

	/**
	 * Save merchant details.
	 *
	 * @param  MerchantDetail  $merchantDetails
	 *
	 * @since 2.8.0
	 *
	 * @return bool
	 */
	public static function save( MerchantDetail $merchantDetails ) {
		return update_option( OptionId::$payPalAccountsOptionKey, $merchantDetails->toArray() );
	}

	/**
	 * Delete merchant details.
	 *
	 * @since 2.8.0
	 *
	 * @return bool
	 */
	public static function delete() {
		return delete_option( OptionId::$payPalAccountsOptionKey );
	}

	/**
	 * Get client token for hosted credit card fields.
	 *
	 * @since 2.8.0
	 *
	 * @return string
	 */
	public static function getClientToken() {
		$optionName = 'give_paypal_commerce_client_token';

		if ( $optionValue = get_transient( $optionName ) ) {
			return '';
		}

		$merchant = give( MerchantDetail::class );
		$response = wp_remote_retrieve_body(
			wp_remote_post(
				give( PayPalClient::class )->getApiUrl( '/v1/identity/generate-token' ),
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

		set_transient(
			$optionName,
			$response['clientToken'],
			$response['expiresIn'] - 60 // Expire token before one minute to prevent unnecessary race condition.
		);

		return $response['clientToken'];
	}
}
