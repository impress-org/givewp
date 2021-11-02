<?php

namespace Give\Framework\PaymentGateways\Contracts;

use Give\PaymentGateways\DataTransferObjects\FormData;

interface SubscriptionModuleInterface {
	/**
	 * Handle gateway subscription request
	 *
	 * @unreleased
	 */
	public function handleSubscriptionRequest($donationId, FormData $formData);
}