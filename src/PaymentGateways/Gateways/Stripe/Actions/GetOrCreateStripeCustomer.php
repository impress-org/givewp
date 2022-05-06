<?php

namespace Give\PaymentGateways\Gateways\Stripe\Actions;

use Give\Donations\Models\Donation;
use Give\PaymentGateways\Gateways\Stripe\Exceptions\StripeCustomerException;
use Give_Stripe_Customer;

class GetOrCreateStripeCustomer
{

    /**
     * @unreleased add second param support to function.
     *             This param is optional because we use it only when donor subscribe for recurring donation.
     * @since 2.19.0
     *
     * @return Give_Stripe_Customer
     * @throws StripeCustomerException
     */
    public function __invoke(Donation $donation, $stripePaymentMethodId = '')
    {
        $giveStripeCustomer = new Give_Stripe_Customer($donation->email, $stripePaymentMethodId);

        if (!$giveStripeCustomer->get_id()) {
            throw new StripeCustomerException(__('Unable to find or create stripe customer object.', 'give'));
        }

        $this->saveStripeCustomerId($donation->id, $giveStripeCustomer->get_id());

        return $giveStripeCustomer;
    }

    /**
     * @since 2.19.0
     *
     * @param int $donationId
     * @param string $stripeCustomerId
     *
     * @return void
     */
    protected function saveStripeCustomerId($donationId, $stripeCustomerId)
    {
        $donor = new \Give_Donor(
            give_get_payment_donor_id($donationId)
        );

        $donor->update_meta(give_stripe_get_customer_key(), $stripeCustomerId);

        give_insert_payment_note(
            $donationId,
            sprintf(__('Stripe Customer ID: %s', 'give'), $stripeCustomerId)
        );

        give_update_meta($donationId, give_stripe_get_customer_key(), $stripeCustomerId);
    }
}
