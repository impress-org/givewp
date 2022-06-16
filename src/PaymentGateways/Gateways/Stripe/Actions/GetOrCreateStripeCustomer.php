<?php

namespace Give\PaymentGateways\Gateways\Stripe\Actions;

use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\PaymentGateways\Gateways\Stripe\Exceptions\StripeCustomerException;
use Give_Stripe_Customer;

class GetOrCreateStripeCustomer
{

    /**
     * @since 2.20.0 add second param support to function.
     *             This param is optional because we use it only when donor subscribe for recurring donation.
     * @since 2.21.0 Update function first argument type to Donation model
     * @since 2.19.0
     *
     * @throws StripeCustomerException|Exception
     */
    public function __invoke(Donation $donation, string $stripePaymentMethodId = ''): Give_Stripe_Customer
    {
        $giveStripeCustomer = new Give_Stripe_Customer($donation->email, $stripePaymentMethodId);

        if (!$giveStripeCustomer->get_id()) {
            throw new StripeCustomerException(__('Unable to find or create stripe customer object.', 'give'));
        }

        $this->saveStripeCustomerId($donation, $giveStripeCustomer->get_id());

        return $giveStripeCustomer;
    }

    /**
     * @since 2.21.0 Update function first argument type to Donation model
     * @since 2.19.0
     * @throws Exception
     */
    protected function saveStripeCustomerId(Donation $donation, string $stripeCustomerId)
    {
        give()->donor_meta->update_meta($donation->donorId, give_stripe_get_customer_key(), $stripeCustomerId);

        DonationNote::create([
            'donationId' => $donation->id,
            'content' => sprintf(__('Stripe Customer ID: %s', 'give'), $stripeCustomerId)
        ]);

        give_update_meta($donation->id, give_stripe_get_customer_key(), $stripeCustomerId);
    }
}
