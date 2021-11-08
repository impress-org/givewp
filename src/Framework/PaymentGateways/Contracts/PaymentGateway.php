<?php

namespace Give\Framework\PaymentGateways\Contracts;

use Give\Framework\LegacyPaymentGateways\Contracts\LegacyPaymentGatewayInterface;

/**
 * @unreleased
 */
abstract class PaymentGateway implements PaymentGatewayInterface, LegacyPaymentGatewayInterface {
	/**
	 * @var SubscriptionModuleInterface $subscriptionModule
	 */
	public $subscriptionModule;

	/**
	 * @unreleased
	 *
	 * @param  SubscriptionModuleInterface|null  $subscriptionModule
	 */
	public function __construct( SubscriptionModuleInterface $subscriptionModule = null ) {
		$this->subscriptionModule = $subscriptionModule;
	}


	/**
	 * @inheritDoc
	 */
	public function supportsSubscriptions() {
		return isset( $this->subscriptionModule );
	}

	/**
	 * If a subscription module isn't wanted this method can be overridden by a child class instead.
	 * Just make sure to override the supportsSubscriptions method as well.
	 *
	 * @inheritDoc
	 */
	public function handleSubscriptionRequest( $donationId, $subscriptionId, $formData ) {
		return $this->subscriptionModule->handleSubscriptionRequest( $donationId, $subscriptionId, $formData );
	}

}
