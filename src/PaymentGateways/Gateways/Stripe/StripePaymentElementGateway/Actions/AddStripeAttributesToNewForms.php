<?php

namespace Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Actions;


use Give\DonationForms\Models\DonationForm;

class AddStripeAttributesToNewForms
{

    /**
     * Add Stripe attributes to the PaymentGateways block new forms.
     */
    public function __invoke(DonationForm $form)
    {
        $block = $form->blocks->findByName('givewp/payment-gateways');
        if ($block) {
            $enabledGateways = array_keys(give_get_option('gateways_v3', []));
            $stripeEnabled = in_array('stripe_payment_element', $enabledGateways, true);

            if ($stripeEnabled) {
                $block->setAttribute('stripeUseGlobalDefault', true);
            }
        }
    }
}
