<?php

namespace Give\PaymentGateways\Gateways\Stripe\Actions;

use Give\Donations\Models\Donation;
use Give\Framework\Exceptions\Primitives\Exception;
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

        $gatewayData['stripePaymentMethod'] = $paymentMethod;

        return $gatewayData;
    }
}
