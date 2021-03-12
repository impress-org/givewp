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
	 * Return whether or not apple Stripe application fee.
	 *
	 * @unreleased
	 *
	 * @return bool
	 */
	public function canApply() {
		return true;
	}
}
