<?php

namespace Give\PaymentGateways\Gateways\Stripe\Traits\StripeApi;

use Give\PaymentGateways\Stripe\ApplicationFee;
use GiveStripe\PaymentMethods\Exceptions\StripeApiException;
use Stripe\Charge;

/**
 * @unreleased
 */
trait CreateCharge
{
    /**
     * @unreleased
     *
     * @param array $stripeChargeRequestArgs
     *
     * @return Charge
     * @throws StripeApiException
     */
    public function createCharge($donationId, $stripeChargeRequestArgs)
    {
        // Set App Info to Stripe.
        give_stripe_set_app_info();

        try {
            // Charge application fee, only if the Stripe premium add-on is not active.
            if (ApplicationFee::canAddfee()) {
                // Set Application Fee Amount.
                $stripeChargeRequestArgs['application_fee_amount'] = give_stripe_get_application_fee_amount(
                    $stripeChargeRequestArgs['amount']
                );
            }

            return Charge::create(
                $stripeChargeRequestArgs,
                give_stripe_get_connected_account_options()
            );
        } catch (\Exception $e) {
            throw new StripeApiException(
                sprintf(
                /* translators: %s Exception Error Message */
                    __('Unable to create a successful charge. Details: %s', 'give'),
                    $e
                )
            );
        }
    }
}
