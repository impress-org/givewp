<?php

namespace Give\PaymentGateways;

interface PaymentGateway {

	/**
	 * Returns a unique ID for the gateway
	 *
	 * @since 2.8.0
	 *
	 * @return string
	 */
	public function getId();

	/**
	 * Returns a human readable name for the gateway
	 *
	 * @since 2.8.0
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Returns a human readable label for use when a donor selects a payment method to use
	 *
	 * @since 2.8.0
	 *
	 * @return string
	 */
	public function getPaymentMethodLabel();
}
