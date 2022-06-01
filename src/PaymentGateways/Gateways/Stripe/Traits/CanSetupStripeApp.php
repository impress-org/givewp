<?php

namespace Give\PaymentGateways\Gateways\Stripe\Traits;

use Stripe\Stripe;

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
    protected function setupStripeApp(int $formId)
    {
        $stripeApiVersion = '2019-05-16';

        Stripe::setApiVersion($stripeApiVersion);
        give_stripe_set_app_info($formId);
    }
}
