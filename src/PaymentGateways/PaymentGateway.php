<?php

namespace Give\PaymentGateways;

/**
 * Interface PaymentGateway
 *
 * For use when defining a Payment Gateway. This gives the basic configurations needed to register
 * the gateway with GiveWP.
 *
 * @since 2.9.0
 */
interface PaymentGateway {
	/**
	 * Returns a unique ID for the gateway
	 *
	 * @since 2.9.0
	 *
	 * @return string
	 */
	public function getId();

	/**
	 * Returns a human readable name for the gateway
	 *
	 * @since 2.9.0
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Returns a human readable label for use when a donor selects a payment method to use
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

	/**
	 * Bootstrap payment gateway
	 *
	 * @since 2.9.0
	 */
	public function boot();
}
