<?php

namespace Give\PaymentGateways\Gateways\Stripe\Actions;

use Give\Donations\Models\Donation;
use Give\PaymentGateways\Gateways\Stripe\Exceptions\PaymentMethodException;
use Give\PaymentGateways\Gateways\Stripe\ValueObjects\PaymentMethod;

class GetPaymentMethodFromRequest
{
    /**
     * @since 2.19.0
     *
     * @throws PaymentMethodException
     */
    public function __invoke(Donation $donation): PaymentMethod
    {
        if (!isset($_POST['give_stripe_payment_method'])) {
            throw new PaymentMethodException('Payment Method Not Found');
        }

        $paymentMethod = new PaymentMethod(
            give_clean($_POST['give_stripe_payment_method'])
        );

        give_update_meta($donation->id, '_give_stripe_source_id', $paymentMethod->id());
        give_insert_payment_note(
            $donation->id,
            sprintf(__('Stripe Source/Payment Method ID: %s', 'give'), $paymentMethod->id())
        );

        return $paymentMethod;
    }
}
