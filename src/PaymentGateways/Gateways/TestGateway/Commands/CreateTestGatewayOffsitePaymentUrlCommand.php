<?php

namespace Give\PaymentGateways\Gateways\TestGateway\Commands;

use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;

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
     * @param GatewayPaymentData $gatewayPaymentData
     *
     * @return string
     */
    public function __invoke( GatewayPaymentData $gatewayPaymentData )
    {
        return '';
    }
}
