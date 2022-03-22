<?php

namespace Give\PaymentGateways\Gateways\Stripe\Traits\StripeApi;

use Exception;
use Give\PaymentGateways\Gateways\Stripe\Exceptions\StripeApiRequestException;
use Give\PaymentGateways\Stripe\ApplicationFee;
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
     * @throws StripeApiRequestException
     */
    public function createCharge($stripeChargeRequestArgs)
    {
        give_stripe_set_app_info();

        try {
            // Charge application fee, only if the Stripe premium add-on is not active.
            if (ApplicationFee::canAddfee()) {
                $stripeChargeRequestArgs['application_fee_amount'] = give_stripe_get_application_fee_amount(
                    $stripeChargeRequestArgs['amount']
                );
            }

            return Charge::create(
                $stripeChargeRequestArgs,
                give_stripe_get_connected_account_options()
            );
        } catch (Exception $e) {
            throw new StripeApiRequestException(
                sprintf(
                /* translators: 1: Exception Error Message */
                    esc_html__('Unable to create a successful charge. Details: %1$s', 'give'),
                    $e->getMessage()
                )
            );
        }
    }
}
