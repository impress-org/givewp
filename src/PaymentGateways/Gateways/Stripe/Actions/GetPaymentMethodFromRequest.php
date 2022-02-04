<?php

namespace Give\PaymentGateways\Gateways\Stripe\Actions;

use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\Gateways\Stripe\Exceptions\PaymentMethodException;
use Give\PaymentGateways\Gateways\Stripe\ValueObjects\PaymentMethod;
use Give\PaymentGateways\Gateways\Stripe\WorkflowAction;

class GetPaymentMethodFromRequest extends WorkflowAction
{
    public function __invoke( GatewayPaymentData $paymentData )
    {
        $paymentMethod = new PaymentMethod( give_clean(
            isset( $_POST['give_stripe_payment_method'] ) ? $_POST['give_stripe_payment_method'] : 0
        ) );

        if( $paymentMethod->id() ) {
            give_update_meta($paymentData->donationId, '_give_stripe_source_id', $paymentMethod->id());
            give_insert_payment_note($paymentData->donationId, sprintf(__('Stripe Source/Payment Method ID: %s', 'give'), $paymentMethod->id()));
        }

        $this->bind( $paymentMethod );
    }
}
