<?php
namespace Give\Framework\LegacyPaymentGateways\Contracts;

/**
 * @since 2.18.0
 */
interface LegacyPaymentGatewayInterface {

	/**
	 * Returns form fields for donation form to render
	 *
	 * @since 2.18.0
	 *
	 * @return string|bool
	 */
	public function getLegacyFormFieldMarkup( $formId, $args );
}
