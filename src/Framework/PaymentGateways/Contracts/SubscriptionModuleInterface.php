<?php

namespace Give\Framework\PaymentGateways\Contracts;

use Give\PaymentGateways\DataTransferObjects\FormData;
use Give\PaymentGateways\DataTransferObjects\SubscriptionData;

interface SubscriptionModuleInterface {
	/**
	 * Handle gateway subscription request
	 *
	 * @unreleased
	 */
	public function handleSubscriptionRequest( $donationId, FormData $formData, SubscriptionData $subscriptionData );
}