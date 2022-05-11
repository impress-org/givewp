<?php

namespace Give\Framework\PaymentGateways\Traits;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\SubscriptionModule;

/**
 * @since 2.20.0
 * @property SubscriptionModule $subscriptionModule
 * @property array $routeMethods
 * @property array $secureRouteMethods
 */
trait HasRouteMethods
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

    /**
     * @since 2.20.0
     *
     * @param string $method
     *
     * @return bool
     */
    public function supportsMethodRoute($method)
    {
        $allGatewayMethods = array_merge($this->routeMethods, $this->secureRouteMethods);

        return in_array($method, $allGatewayMethods, true);
    }

    /**
     * @since 2.20.0
     *
     * @param string $method
     *
     * @throws Exception
     */
    public function callRouteMethod($method, $queryParams)
    {
        if ($this->supportsMethodRoute($method)) {
            return $this->$method($queryParams);
        }

        throw new PaymentGatewayException(
            sprintf(
                '%1$s route method is not supported by %2$s and %3$s',
                $method,
                get_class($this),
                get_class($this->subscriptionModule)
            )
        );
    }
}
