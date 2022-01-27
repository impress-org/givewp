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
    public function __invoke(OffsiteGatewayPaymentData $offsiteGatewayPaymentData)
    {
        // Test offsite payment flow:
        // 1) Setup a second website (http://freshdb.test).
        // 2) Add below code as in mu plugin.
        // add_action( 'init', function(){
        //     if( ! isset( $_GET['test-offsite-redirect'] ) ) {
        //        return;
        //     }
        //
        //     $redirect = urldecode($_GET['redirect']);
        //
        //     wp_redirect($redirect);
        //     exit();
        // });
        // 3) Process payment with "Test Gateway Offsite"
        //
        // If you want to test failed or cancelled donation flow then change url to:
        // $offsiteGatewayPaymentData->failedRedirectUrl or $offsiteGatewayPaymentData->cancelledRedirectUrl
        return sprintf(
            '%1$s?test-offsite-redirect=1&redirect=%2$s',
            'http://freshdb.test',
            urlencode($offsiteGatewayPaymentData->failedRedirectUrl)
        );
    }
}
