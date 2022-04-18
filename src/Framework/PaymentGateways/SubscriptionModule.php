<?php

namespace Give\Framework\PaymentGateways;

use Give\Framework\PaymentGateways\Contracts\SubscriptionModuleInterface;

/**
 * @unreleased
 */
abstract class SubscriptionModule implements SubscriptionModuleInterface
{
    /**
     * Route methods are used to extend the gateway api.
     * By adding a custom routeMethod, you are effectively
     * registering a new public route url that will resolve itself and
     * call your method.
     *
     * @var string[]
     */
    public $routeMethods = [];

    /**
     * Secure Route methods are used to extend the gateway api with an additional wp_nonce.
     * By adding a custom secureRouteMethod, you are effectively
     * registering a new route url that will resolve itself and
     * call your method after validating the nonce.
     *
     * @var string[]
     */
    public $secureRouteMethods = [];
}
