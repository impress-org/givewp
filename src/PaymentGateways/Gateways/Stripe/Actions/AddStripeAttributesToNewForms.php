<?php

namespace Give\PaymentGateways\Gateways\Stripe\Actions;


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
            $block->setAttribute('stripeUseGlobalDefault', true);
        }
    }
}
