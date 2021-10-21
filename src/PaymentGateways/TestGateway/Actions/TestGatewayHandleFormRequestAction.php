<?php

namespace Give\PaymentGateways\TestGateway\Actions;

/**
 * Class TestGatewayHandleFormRequestAction
 * @unreleased
 */
class TestGatewayHandleFormRequestAction {
	/**
	 * @unreleased
	 *
	 * @return void
	 */
	public function __invoke( $donationId ) {
		give_update_payment_status( $donationId, 'publish' );
		give_send_to_success_page();
	}
}
