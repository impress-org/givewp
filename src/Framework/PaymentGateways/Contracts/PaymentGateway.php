<?php

namespace Give\Framework\PaymentGateways\Contracts;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
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
	 * @inheritDoc
	 */
	public function mountSubscriptionModule( $subscriptionModuleClass ) {
		if ( ! class_implements( $subscriptionModuleClass, SubscriptionModuleInterface::class ) ) {
			throw new InvalidArgumentException( sprintf(
				'%1$s must implement %2$s',
				$subscriptionModuleClass,
				SubscriptionModuleInterface::class
			) );
		}

		$this->subscriptionModule = give( $subscriptionModuleClass );
	}


	/**
	 * @inheritDoc
	 */
	public function hasSubscriptionModule() {
		return isset( $this->subscriptionModule );
	}

	/**
	 * @inheritDoc
	 */
	public function handleSubscriptionRequest( $donationId, $subscriptionId, $formData ) {
		return $this->subscriptionModule->handleSubscriptionRequest( $donationId, $subscriptionId, $formData );
	}

}
