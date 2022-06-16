<?php

namespace Give\Helpers\Gateways;

use Give\Framework\PaymentGateways\PaymentGateway;
use ReflectionException;
use ReflectionMethod;

/**
 * @unreleased
 */
class Utils
{
    /**
     * @unreleased
     * @throws ReflectionException
     */
    public static function isFunctionImplementedInGatewayClass(PaymentGateway $gateway, string $method): bool
    {
        $reflector = new ReflectionMethod($gateway, $method);
        return ($reflector->getDeclaringClass()->getName() === get_class($gateway));
    }
}
