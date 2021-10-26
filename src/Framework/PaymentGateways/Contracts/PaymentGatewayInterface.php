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
	 * @unreleased
	 *
	 * @return string
	 */
	public static function id();

	/**
	 * Return a unique identifier for the migration
	 *
	 * @unreleased
	 *
	 * @return string
	 */
	public function getId();

	/**
	 * Returns a human-readable name for the gateway
	 *
	 * @unreleased
	 *
	 * @return string - Translated text
	 */
	public function getName();

	/**
	 * Returns a human-readable label for use when a donor selects a payment method to use
	 *
	 * @unreleased
	 *
	 * @return string - Translated text
	 */
	public function getPaymentMethodLabel();

	/**
	 * After creating the initial payment, we can continue with the gateway processing
	 *
	 * @unreleased
	 *
	 * @param  int  $donationId
	 * @param  FormData  $formData
	 *
	 * @return string|bool
	 */
	public function handleGatewayRequest( $donationId, $formData );
}
