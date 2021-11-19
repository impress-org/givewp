<?php

namespace Give\PaymentGateways\Stripe;

/**
 * Class DonationFormElements
 * @package Give\PaymentGateways\Stripe
 *
 * We use this class to output HTML fields on donation form.
 *
 * @since 2.9.2
 */
class DonationFormElements
{
    /**
     * Add html tags to form .
     *
     * @since 2.9.2
     *
     * @param array $htmlTags Array of form html tags
     *
     * @return array
     */
    public function addFormHtmlTags($htmlTags)
    {
        if (give_is_gateway_active('stripe_checkout')) {
            $htmlTags['data-stripe-checkout-type'] = give_stripe_get_checkout_type();
        }

        return $htmlTags;
    }
}
