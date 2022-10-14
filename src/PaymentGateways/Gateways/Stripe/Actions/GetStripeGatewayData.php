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
     * Returns $gatewayData array to be used in stripe gateways.
     *
     * @unreleased add try / catch with exception logging
     */
    public function __invoke($gatewayData, Donation $donation): array
    {
        try {
            $paymentMethod = (new GetPaymentMethodFromRequest())($donation);
            /**
             * This supplies the actual Stripe Api PaymentMethod
             */
            $gatewayData['paymentMethod'] = $paymentMethod;
            /**
             * This internal ValueObject is currently being used throughout the Stripe gateways.
             * Eventually this array key will be @deprecated in favor of using Stripe's PaymentMethod
             * Object directly via $gatewayData['paymentMethod']
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
