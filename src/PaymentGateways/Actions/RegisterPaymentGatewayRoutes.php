<?php

namespace Give\PaymentGateways\Actions;

use Give\Framework\PaymentGateways\Routes\ReturnFromOffsiteRedirectRoute;
use Give\Helpers\Hooks;

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
            Hooks::addAction('template_redirect', $route);
        }
    }
}
