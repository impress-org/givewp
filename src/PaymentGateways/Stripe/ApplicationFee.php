<?php

namespace Give\PaymentGateways\Stripe;

/**
 * Class ApplicationFee
 * @package Give\PaymentGateways\Stripe
 *
 * @unreleased
 */
class ApplicationFee {
	/**
	 * @unreleased
	 *
	 * @return bool
	 */
	public function canAddFee() {
		return ! $this->isStripeProAddonActive();
	}

	/**
	 * @unreleased
	 * @return bool
	 */
	public function isStripeProAddonActive() {
		return defined( 'GIVE_STRIPE_VERSION' );
	}
}
