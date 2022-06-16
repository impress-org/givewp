<?php

namespace Give\PaymentGateways\Gateways\Stripe\Traits;

/**
 * @since 2.21.0
 */
trait CanSetupStripeApp
{
    /**
     * @since 2.21.0
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
