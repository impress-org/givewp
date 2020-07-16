<?php

namespace Give\PaymentGateways\PayPalCheckout;

use Give\PaymentGateways\PaymentGateway;

class PayPalCheckout implements PaymentGateway {
	/**
	 * @inheritDoc
	 */
	public function getId() {
		return 'paypal-checkout';
	}

	/**
	 * @inheritDoc
	 */
	public function getName() {
		return esc_html__( 'PayPal Checkout', 'give' );
	}

	/**
	 * @inheritDoc
	 */
	public function getPaymentMethodLabel() {
		return esc_html__( 'Credit Card', 'give' );
	}

	/**
	 * @inheritDoc
	 */
	public function getOptions() {
		return [
			[
				'type'       => 'title',
				'id'         => 'give_title_gateway_settings_2',
				'table_html' => false,
			],
			[
				'name' => esc_html__( 'Connect With Paypal', 'give' ),
				'id'   => 'paypal_checkout_account_manger',
				'type' => 'paypal_checkout_account_manger',
			],
			[
				'type'       => 'sectionend',
				'id'         => 'give_title_gateway_settings_2',
				'table_html' => false,
			],
		];
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {
		( new ScriptLoader() )->boot();
		( new AjaxRequestHandler() )->boot();
	}
}
