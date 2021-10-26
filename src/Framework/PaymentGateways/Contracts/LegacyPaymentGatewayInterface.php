<?php
namespace Give\Framework\PaymentGateways\Contracts;

/**
 * @unreleased
 */
interface LegacyPaymentGatewayInterface {

	/**
	 * Returns form fields for donation form to render
	 *
	 * @unreleased
	 *
	 * @return string|bool
	 */
	public function getLegacyFormFieldMarkup( $formId );
}
