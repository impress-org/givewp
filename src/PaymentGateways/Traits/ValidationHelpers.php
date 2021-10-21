<?php

namespace Give\PaymentGateways\Traits;

trait ValidationHelpers {
	/**
	 * Validate request
	 *
	 * @param  string  $gatewayNonce
	 */
	private function validateGatewayNonce( $gatewayNonce ) {
		if ( ! wp_verify_nonce( $gatewayNonce, 'give-gateway' ) ) {
			wp_die( esc_html__( 'We\'re unable to recognize your session. Please refresh the screen to try again; otherwise contact your website administrator for assistance.',
				'give' ), esc_html__( 'Error', 'give' ), [ 'response' => 403 ] );
		}
	}
}