<?php

namespace Give\PaymentGateways\Gateways\Stripe\Actions;

use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\PaymentGateways\Gateways\Stripe\Exceptions\PaymentMethodException;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentMethod as StripePaymentMethod;

class GetPaymentMethodFromRequest
{
    /**
     * @unreleased use Stripe's PaymentMethod class
     * @since 2.19.0
     *
     * @throws PaymentMethodException|Exception|ApiErrorException
     */
    public function __invoke(Donation $donation): StripePaymentMethod
    {
        if (!isset($_POST['give_stripe_payment_method'])) {
            throw new PaymentMethodException('Payment Method Not Found');
        }

        $giveStripePaymentMethodId = give_clean($_POST['give_stripe_payment_method']);

        $giveStripeConnectedAccountId = give_stripe_get_connected_account_id($donation->formId);

        $paymentMethod = StripePaymentMethod::retrieve(
            $giveStripePaymentMethodId,
            ['stripe_account' => $giveStripeConnectedAccountId]
        );

        give_update_meta($donation->id, '_give_stripe_source_id', $paymentMethod->id);

        DonationNote::create([
            'donationId' => $donation->id,
            'content' => sprintf(__('Stripe Source/Payment Method ID: %s', 'give'), $paymentMethod->id)
        ]);

        return $paymentMethod;
    }
}
