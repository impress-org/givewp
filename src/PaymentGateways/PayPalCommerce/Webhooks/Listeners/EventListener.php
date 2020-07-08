<?php

namespace Give\PaymentGateways\PayPalCommerce\Webhooks\Listeners;

interface EventListener {
	/**
	 * This processes the PayPal Commerce webhook event passed to it.
	 *
	 * @param object $event
	 *
	 * @return void
	 */
	public function processEvent( $event);
}
