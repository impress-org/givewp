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
	 */
	public function getLegacyFormFieldMarkup( int $formId, array $args ): string;
}
