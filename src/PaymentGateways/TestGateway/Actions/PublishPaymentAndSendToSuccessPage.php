<?php

namespace Give\PaymentGateways\TestGateway\Actions;

use Give\Framework\Http\Response;

/**
 * Class PublishPaymentAndSendToSuccessPage
 *
 * @unreleased
 */
class PublishPaymentAndSendToSuccessPage {
	/**
	 * @unreleased
	 *
	 * @return Response
	 */
	public function __invoke( $donationId, $gateway ) {
		give_update_payment_status( $donationId, 'publish' );

		$redirect = give_get_success_page_uri();

		return Response::redirect(apply_filters( 'give_success_page_redirect', $redirect, $gateway));
	}
}
