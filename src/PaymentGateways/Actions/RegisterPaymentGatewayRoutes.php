<?php

namespace Give\PaymentGateways\Actions;

use Give\Framework\PaymentGateways\Routes\GatewayRoute;
use Give\Framework\PaymentGateways\Routes\ReturnFromOffsiteRedirectRoute;

class RegisterPaymentGatewayRoutes
{
    private $routes = [
        ReturnFromOffsiteRedirectRoute::class
    ];

    /**
     * @unreleased
     */
    public function __invoke()
    {
        foreach ($this->routes as $route){
            /** @var GatewayRoute $gatewayRoute */
            $gatewayRoute = give($route);

            $gatewayRoute->init();
        }
    }
}
