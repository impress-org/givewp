<?php

namespace Give\Framework\PaymentGateways;

use Give\Container\Container;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\LegacyPaymentGateways\Adapters\LegacyPaymentGatewayRegisterAdapter;
use Give\Framework\PaymentGateways\Contracts\PaymentGatewaysIterator;
use Give\Framework\PaymentGateways\Exceptions\OverflowException;

/**
 * @since 2.18.0
 */
class PaymentGatewayRegister extends PaymentGatewaysIterator
{
    protected $gateways = [];

    /**
     * Get Gateways
     *
     * @since 2.18.0
     *
     * @return array
     */
    public function getPaymentGateways()
    {
        return $this->gateways;
    }

    /**
     * Get Gateway
     *
     * @since 2.18.0
     *
     * @param string $id
     *
     * @return string
     */
    public function getPaymentGateway($id)
    {
        if (!$this->hasPaymentGateway($id)) {
            throw new InvalidArgumentException("No gateway exists with the ID {$id}");
        }

        return $this->gateways[$id];
    }

    /**
     * @since 2.18.0
     *
     * @param string $id
     *
     * @return bool
     */
    public function hasPaymentGateway($id)
    {
        return isset($this->gateways[$id]);
    }

    /**
     * Register Gateway
     *
     * @since 2.18.0
     *
     * @param string $gatewayClass
     *
     * @throws OverflowException|InvalidArgumentException|Exception
     */
    public function registerGateway($gatewayClass)
    {
        if (!is_subclass_of($gatewayClass, PaymentGateway::class)) {
            throw new InvalidArgumentException(
                sprintf(
                    '%1$s must extend %2$s',
                    $gatewayClass,
                    PaymentGateway::class
                )
            );
        }

        $gatewayId = $gatewayClass::id();

        if ($this->hasPaymentGateway($gatewayId)) {
            throw new OverflowException("Cannot register a gateway with an id that already exists: $gatewayId");
        }

        $this->gateways[$gatewayId] = $gatewayClass;

        $this->registerGatewayWithServiceContainer($gatewayClass, $gatewayId);

        $this->afterGatewayRegister($gatewayClass);
    }

    /**
     * Unregister Gateway
     *
     * @since 2.18.0
     *
     * @param $gatewayId
     */
    public function unregisterGateway($gatewayId)
    {
        if (isset($this->gateways[$gatewayId])) {
            unset($this->gateways[$gatewayId]);
        }
    }

    /**
     *
     * Register Gateway with Service Container as Singleton
     * with option of adding Subscription Module through filter "give_gateway_{$gatewayId}_subscription_module"
     *
     * @since 2.18.0
     *
     * @param string $gatewayClass
     * @param string $gatewayId
     *
     * @return void
     */
    private function registerGatewayWithServiceContainer($gatewayClass, $gatewayId)
    {
        give()->singleton($gatewayClass, function (Container $container) use ($gatewayClass, $gatewayId) {
            $subscriptionModule = apply_filters("give_gateway_{$gatewayId}_subscription_module", null);

            /* @var PaymentGateway $gateway */
            $gateway = new $gatewayClass($subscriptionModule ? $container->make($subscriptionModule) : null);
            $this->registerSubscriptionModuleRoutes($gateway);

            return $gateway;
        });
    }

    /**
     * After gateway is registered, connect to legacy payment gateway adapter
     *
     * @param string $gatewayClass
     */
    private function afterGatewayRegister($gatewayClass)
    {
        /** @var LegacyPaymentGatewayRegisterAdapter $legacyPaymentGatewayRegisterAdapter */
        $legacyPaymentGatewayRegisterAdapter = give(LegacyPaymentGatewayRegisterAdapter::class);

        $legacyPaymentGatewayRegisterAdapter->connectGatewayToLegacyPaymentGatewayAdapter($gatewayClass);
    }

    /**
     * @unreleased
     * @return void
     */
    private function registerSubscriptionModuleRoutes(PaymentGateway $gateway)
    {
        foreach ($gateway->subscriptionModule->routeMethods as $routeMethod) {
            $this->register3rdPartyRouteMethod(
                $gateway,
                $routeMethod,
                get_class($gateway->subscriptionModule)
            );
        }

        foreach ($gateway->subscriptionModule->secureRouteMethods as $routeMethod) {
            $this->register3rdPartyRouteMethod(
                $gateway,
                $routeMethod,
                get_class($gateway->subscriptionModule),
                true
            );
        }
    }

    /**
     * @unreleased
     *
     * @param PaymentGateway $gateway
     * @param string $methodName
     * @param string $className
     * @param bool $secureRoute
     */
    private function register3rdPartyRouteMethod(PaymentGateway $gateway, $methodName, $className, $secureRoute = false)
    {
        // Do not register duplicate routes.
        if (
            isset($gateway->secureRouteMethods[$methodName]) ||
            isset($gateway->routeMethods[$methodName])
        ) {
            return;
        }

        $callback = [$className, $methodName];
        if ($secureRoute) {
            $gateway->secureRouteMethods[$methodName] = $callback;
        } else {
            $gateway->routeMethods[$methodName] = $callback;
        }
    }

    /**
     * @unreleased
     *
     * @param PaymentGateway $gateway
     * @param string $methodName
     */
    private function deRegister3rdPartyRouteMethod(PaymentGateway $gateway, $methodName)
    {
        // Do not de-register other than 3rd party routes.
        if (method_exists($gateway, $methodName)) {
            return;
        }

        if (isset($gateway->routeMethods[$methodName])) {
            unset($gateway->routeMethods[$methodName]);
        } elseif (isset($gateway->secureRouteMethods[$methodName])) {
            unset($gateway->secureRouteMethods[$methodName]);
        }
    }
}
