<?php

namespace Give\Framework\PaymentGateways\Traits;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\SubscriptionModule;

/**
 * @unreleased
 * @property SubscriptionModule $subscriptionModule
 * @property array $routeMethods
 * @property array $secureRouteMethods
 */
trait CallRouteMethod
{
    /**
     * @unreleased
     *
     * @param string $method
     *
     * @return bool
     */
    public function supportsMethodRoute($method)
    {
        $allGatewayMethods = array_merge($this->routeMethods, $this->secureRouteMethods);

        return in_array($method, $allGatewayMethods);
    }

    /**
     * @unreleased
     *
     * @param string $method
     *
     * @throws Exception
     */
    public function callRouteMethod($method, $queryParams)
    {
        if ($this->supportsMethodRoute($method)) {
            return $this->$method($method, $queryParams);
        }

        if ($this->subscriptionModule->supportsMethodRoute($method)) {
            return $this->subscriptionModule->callRouteMethod($method, $queryParams);
        }

        throw new Exception(
            sprintf(
                '%1$s route method is not supported by %2$s and %3$s',
                $method,
                get_class($this),
                get_class($this->subscriptionModule)
            )
        );
    }

}
