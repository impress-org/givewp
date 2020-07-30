<?php
namespace Give\PaymentGateways\PayPalCommerce\Repositories;

use Give\PaymentGateways\PayPalCommerce\MerchantDetail;
use Give\PaymentGateways\PayPalCommerce\OptionId;

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
}
