<?php

namespace Give\PaymentGateways\Gateways\Stripe\Actions;

use Give\Donations\Models\Donation;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Log\PaymentGatewayLog;
use Give\PaymentGateways\Gateways\Stripe\ValueObjects\PaymentMethod;
use Stripe\Exception\ApiErrorException;

class GetStripeGatewayData
{
    /**
     * Returns gatewayData array to be used in stripe gateways.
     * This will eventually be moved into core
     *
     * @unreleased add try / catch with exception logging
     */
    public function __invoke($gatewayData, Donation $donation): array
    {
        try {
            $paymentMethod = (new GetPaymentMethodFromRequest())($donation);
            $gatewayData['stripePaymentMethod'] = $paymentMethod;
            /**
             * @deprecated use 'paymentMethod'
             */
            $gatewayData['stripePaymentMethod'] = new PaymentMethod($paymentMethod->id);

            return $gatewayData;
        } catch (ApiErrorException $exception) {
            PaymentGatewayLog::error($exception->getMessage(), [
                'StripeCode' => $exception->getStripeCode()
            ]);
        } catch (Exception $exception) {
            PaymentGatewayLog::error($exception->getMessage());
        }

        return $gatewayData;
    }
}
