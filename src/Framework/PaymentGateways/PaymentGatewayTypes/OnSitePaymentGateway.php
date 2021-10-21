<?php

namespace Give\Framework\PaymentGateways\PaymentGatewayTypes;

/**
 * @unreleased
 */
interface OnSitePaymentGateway {

	/**
	 * Handles form request
	 *
	 * @unreleased
	 *
	 * @param $request
	 *
	 * @return mixed
	 */
	public function handleFormRequest( $request );
}
