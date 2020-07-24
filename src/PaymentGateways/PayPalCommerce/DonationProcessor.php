<?php
namespace Give\PaymentGateways\PayPalCommerce;

/**
 * Class DonationProcessor
 * @package Give\PaymentGateways\PayPalCommerce
 *
 * @since 2.8.0
 */
class DonationProcessor {
	/**
	 * Bootstrap class.
	 *
	 * @since 2.8.0
	 */
	public function boot() {
		$gatewayId = give( PayPalCommerce::class )->getId();
		add_action( "give_gateway_{$gatewayId}", [ $this, 'handle' ] );

		return $this;
	}

	/**
	 * Handle donation form submission.
	 *
	 * @since 2.8.0
	 */
	public function handle() {
		printf(
			'<pre>$1%s</pre>',
			print_r( $_POST, true )
		);

		exit();
	}
}
