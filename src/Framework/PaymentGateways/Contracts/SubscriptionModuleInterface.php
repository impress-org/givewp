<?php

namespace Give\Framework\PaymentGateways\Contracts;

use Give\PaymentGateways\DataTransferObjects\FormData;

interface SubscriptionModuleInterface {
	/**
	 * Handle gateway subscription request
	 *
	 * @param  int  $donationId
	 * @param  int  $subscriptionId
	 * @param  FormData  $formData
	 *
	 * @unreleased
	 */
	public function handleSubscriptionRequest( $donationId, $subscriptionId, $formData );
}