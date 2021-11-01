<?php

namespace Give\Framework\PaymentGateways\Contracts;

use Give\Framework\LegacyPaymentGateways\Contracts\LegacyPaymentGatewayInterface;

/**
 * @unreleased
 */
abstract class PaymentGateway implements PaymentGatewayInterface, LegacyPaymentGatewayInterface {
	public $subscriptionModule;

	public function mountSubscriptionModule( $subscriptionClass ) {
		$this->subscriptionModule = $subscriptionClass;
	}
	
	public function getSubscriptionModule() {
		return give( $this->subscriptionModule );
	}

}
