<?php

namespace Give\PaymentGateways\Gateways\Stripe\Actions;

use Give\Donations\Models\Donation;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\PaymentGateways\Gateways\Stripe\ValueObjects\PaymentMethod;
use Stripe\Exception\ApiErrorException;

class GetStripeGatewayData
{
    /**
     * @unreleased
     *
     * @throws Exception
     * @throws ApiErrorException
     */
    public function __invoke($gatewayData, Donation $donation): array
    {
        $paymentMethod = (new GetPaymentMethodFromRequest())($donation);

        $gatewayData['paymentMethod'] = $paymentMethod;
        
        /**
         * @deprecated use 'paymentMethod'
         */
        $gatewayData['stripePaymentMethod'] = new PaymentMethod($paymentMethod->id);

        return $gatewayData;
    }
}
