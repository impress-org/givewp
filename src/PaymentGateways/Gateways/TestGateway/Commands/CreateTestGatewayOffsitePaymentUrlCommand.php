<?php

namespace Give\PaymentGateways\Gateways\TestGateway\Commands;

use Give\Framework\PaymentGateways\Commands\GatewayCommand;
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
        return sprintf(
            '%1$s?test-offsite-redirect=1&redirect=%2$s',
            'http://freshdb.test',
            urlencode( $offsiteGatewayPaymentData->redirectUrl )
        );
    }
}
