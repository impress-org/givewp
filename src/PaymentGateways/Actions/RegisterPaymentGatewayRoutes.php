<?php

namespace Give\PaymentGateways\Actions;

use Give\Framework\PaymentGateways\Routes\GatewayRoute;

class RegisterPaymentGatewayRoutes
{
    /**
     * @unreleased
     */
    public function __invoke()
    {
        GatewayRoute::offsite()->get('handleReturnFromOffsiteRedirect');
    }
}
