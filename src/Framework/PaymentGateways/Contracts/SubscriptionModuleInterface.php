<?php

namespace Give\Framework\PaymentGateways\Contracts;

use Give\PaymentGateways\DataTransferObjects\FormData;

interface SubscriptionModuleInterface {
	/**
	 * Handle gateway subscription request
	 *
	 * @unreleased
	 *
	 * @param  int  $donationId
	 * @param  int  $subscriptionId
	 * @param  FormData  $formData
	 */
	public function handleSubscriptionRequest( $donationId, $subscriptionId, $formData );
}