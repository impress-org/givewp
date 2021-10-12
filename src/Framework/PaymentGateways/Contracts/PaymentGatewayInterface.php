<?php
namespace Give\Framework\PaymentGateways\Contracts;

/**
 * @unreleased
 */
interface PaymentGatewayInterface {
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
	 * @return string
	 */
	public function getName();

	/**
	 * Returns a human-readable label for use when a donor selects a payment method to use
	 *
	 * @since 2.9.0
	 *
	 * @return string
	 */
	public function getPaymentMethodLabel();

	/**
	 * Get payment gateway options
	 *
	 * @return array
	 */
	public function getOptions();
}
