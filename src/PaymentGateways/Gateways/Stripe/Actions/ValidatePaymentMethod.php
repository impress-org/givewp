<?php

namespace Give\PaymentGateways\Gateways\Stripe\Actions;

use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\Gateways\Stripe\Exceptions\PaymentMethodException;
use Give\PaymentGateways\Gateways\Stripe\ValueObjects\PaymentMethod;
use Give\PaymentGateways\Gateways\Stripe\WorkflowAction;

class ValidatePaymentMethod extends WorkflowAction
{
    public function __invoke( PaymentMethod $paymentMethod )
    {
        if( ! $paymentMethod->id() ) {
            throw new PaymentMethodException('Payment Method Not Found');
        }
    }
}
