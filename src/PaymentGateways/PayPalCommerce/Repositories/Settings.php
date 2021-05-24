<?php

namespace Give\PaymentGateways\PayPalCommerce\Repositories;

class Settings {
	/**
	 * wp_options key for the account country
	 *
	 * @since 2.9.0
	 */
	const COUNTRY_KEY = 'paypal_commerce_account_country';

	/**
	 * wp_options key for the access token
	 *
	 * @since 2.9.0
	 */
	const ACCESS_TOKEN_KEY = 'temp_give_paypal_commerce_seller_access_token';

	/**
	 * wp_options key for the partner link details
	 *
	 * @since 2.9.0
	 */
	const PARTNER_LINK_DETAIL_KEY = 'temp_give_paypal_commerce_partner_link';

	/**
	 * give_settings key for the collect bulling details
	 *
	 * @since 2.11.1
	 */
	const COLLECT_BILLING_DETAILS_KEY = 'paypal_commerce_collect_billing_details';

	/**
	 * Returns the country for the account
	 *
	 * @since 2.9.0
	 *
	 * @return string|null
	 */
	public function getAccountCountry() {
		return get_option( self::COUNTRY_KEY, give_get_country() );
	}

	/**
	 * Returns the account access token
	 *
	 * @since 2.9.0
	 *
	 * @return array|null
	 */
	public function getAccessToken() {
		return get_option( self::ACCESS_TOKEN_KEY, null );
	}

	/**
	 * Updates the country account
	 *
	 * @param string $country
	 *
	 * @return bool
	 */
	public function updateAccountCountry( $country ) {
		return update_option( self::COUNTRY_KEY, $country );
	}

	/**
	 * Updates the account access token
	 *
	 * @param $token
	 *
	 * @return bool
	 */
	public function updateAccessToken( $token ) {
		return update_option( self::ACCESS_TOKEN_KEY, $token );
	}

	/**
	 * Deletes the account access token
	 *
	 * @return bool
	 */
	public function deleteAccessToken() {
		return delete_option( self::ACCESS_TOKEN_KEY );
	}

	/**
	 * Returns the partner link details
	 *
	 * @since 2.9.0
	 *
	 * @return string|null
	 */
	public function getPartnerLinkDetails() {
		return get_option( self::PARTNER_LINK_DETAIL_KEY, null );
	}

	/**
	 * Updates the partner link details
	 *
	 * @param $linkDetails
	 *
	 * @return bool
	 */
	public function updatePartnerLinkDetails( $linkDetails ) {
		return update_option( self::PARTNER_LINK_DETAIL_KEY, $linkDetails );
	}

	/**
	 * Deletes the partner link details
	 *
	 * @return bool
	 */
	public function deletePartnerLinkDetails() {
		return delete_option( self::PARTNER_LINK_DETAIL_KEY );
	}

	/**
	 * Deletes the partner link details
	 *
	 * @since 2.11.1
	 * @return bool
	 */
	public function canCollectBillingInformation() {
		return give_is_setting_enabled( give_get_option( self::COLLECT_BILLING_DETAILS_KEY ) );
	}
}
