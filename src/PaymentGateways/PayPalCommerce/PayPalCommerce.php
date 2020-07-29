<?php

namespace Give\PaymentGateways\PayPalCommerce;

use Give\PaymentGateways\PaymentGateway;

/**
 * Class PayPalCommerce
 *
 * Boots the PayPalCommerce gateway and provides its basic registration properties
 *
 * @since 2.8.0
 */
class PayPalCommerce implements PaymentGateway {
	/**
	 * @inheritDoc
	 */
	public function getId() {
		return 'paypal-commerce';
	}

	/**
	 * @inheritDoc
	 */
	public function getName() {
		return esc_html__( 'PayPal Donations', 'give' );
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
				'id'   => 'paypal_commerce_account_manger',
				'type' => 'paypal_commerce_account_manger',
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
		give( ScriptLoader::class )->boot();
		give( AjaxRequestHandler::class )->boot();

		// Boot RefreshToken class immediately if cron job running.
		if ( wp_doing_cron() ) {
			give( RefreshToken::class );
		}
	}
}
