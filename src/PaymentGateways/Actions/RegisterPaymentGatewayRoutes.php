<?php

namespace Give\PaymentGateways\Actions;

use Give\Framework\PaymentGateways\Routes\RedirectOffsiteRoute;
use Give\Route\Route;

class RegisterPaymentGatewayRoutes
{
    private $routes = [
        RedirectOffsiteRoute::class
    ];

    /**
     * @unreleased 
     */
    public function __invoke()
    {
        foreach ($this->routes as $route){
            /** @var Route $gatewayRoute */
            $gatewayRoute = give($route);

            $gatewayRoute->init();
        }
    }
}
