<?php

namespace Give\PaymentGateways\Gateways\Stripe\Traits;

/**
 * @unreleased
 */
trait CanSetupStripeApp
{
    /**
     * @unreleased
     *
     * @param int $formId
     *
     * @return void
     */
    protected function setupStripeApp($formId)
    {
        give_stripe_set_app_info($formId);
    }
}
