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
     * @return PaymentGateway
     */
    public function getPaymentGateway($id)
    {
        if (!$this->hasPaymentGateway($id)) {
            throw new InvalidArgumentException("No gateway exists with the ID {$id}");
        }

        /** @var PaymentGateway $gateway */
        $gateway = give($this->gateways[$id]);

        return $gateway;
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
            $subscriptionModule = apply_filters("givewp_gateway_{$gatewayId}_subscription_module", null);

            return new $gatewayClass($subscriptionModule ? $container->make($subscriptionModule) : null);
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
}
