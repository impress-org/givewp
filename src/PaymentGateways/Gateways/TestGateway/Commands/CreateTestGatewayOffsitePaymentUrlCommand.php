<?php

namespace Give\PaymentGateways\Gateways\TestGateway\Commands;

use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\DataTransferObjects\OffsiteGatewayPaymentData;

/**
 * This class uses to build payment url of offsite test gateway.
 *
 * @unreleased
 */
class CreateTestGatewayOffsitePaymentUrlCommand implements GatewayCommand
{
    /**
     * Return payment url.
     *
     * @unreleased
     *
     * @param OffsiteGatewayPaymentData $offsiteGatewayPaymentData
     *
     * @return string
     */
    public function __invoke( OffsiteGatewayPaymentData $offsiteGatewayPaymentData )
    {
        return $offsiteGatewayPaymentData->redirectUrl;
    }
}
