<?php
namespace Give\Framework\PaymentGateways\Contracts;

use Give\PaymentGateways\DataTransferObjects\FormData;

/**
 * @unreleased
 */
interface PaymentGatewayInterface {
	/**
	 * Return a unique identifier for the migration
	 *
	 * @return string
	 */
	public static function id();

	/**
	 * Return a unique identifier for the migration
	 *
	 * @return string
	 */
	public function getId();

	/**
	 * Returns a human-readable name for the gateway
	 *
	 * @since 2.9.0
	 *
	 * @return string - Translated text
	 */
	public function getName();

	/**
	 * Returns a human-readable label for use when a donor selects a payment method to use
	 *
	 * @since 2.9.0
	 *
	 * @return string - Translated text
	 */
	public function getPaymentMethodLabel();

	/**
	 * Returns form fields for donation form to render
	 *
	 * @since 2.9.0
	 *
	 * @return string|bool
	 */
	public function getLegacyFormFieldMarkup( $formId );

	/**
	 * After creating the initial payment, we can continue with the gateway processing
	 *
	 * @since 2.9.0
	 *
	 * @param  int  $donationId
	 * @param  FormData  $formData
	 *
	 * @return string|bool
	 */
	public function handleGatewayRequest( $donationId, $formData );
}
