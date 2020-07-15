<?php
namespace Give\PaymentGateways\PayPalCheckout;

/**
 * Class OptionId
 * @package Give\PaymentGateways\PayPalCheckout
 *
 * @since 2.8.0
 */
class OptionId {

	/**
	 * Option key name.
	 *
	 * In this option we stores PayPal access token details temporary.
	 *
	 * @var string
	 * @since 2.8.0
	 */
	public static $accessTokenOptionKey = 'temp_give_paypal_checkout_seller_access_token';
	/**
	 * Option key name.
	 *
	 * In this option we stores partner link rest api response temporary.
	 *
	 * @var string
	 * @since 2.8.0
	 */
	public static $partnerInfoOptionKey = 'temp_give_paypal_checkout_partner_link';
	/**
	 * Option key name.
	 *
	 * In this option we stores payPal account details.
	 *
	 * @var string
	 * @since 2.8.0
	 */
	public static $payPalAccountsOptionKey = 'give_paypal_checkout_accounts';
}
