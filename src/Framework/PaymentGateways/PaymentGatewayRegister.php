<?php

namespace Give\Framework\PaymentGateways;

use Give\Container\Container;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\PaymentGateways\Contracts\PaymentGatewaysIterator;
use Give\Framework\PaymentGateways\Exceptions\OverflowException;

/**
 * @unreleased add deprecated gateways
 * @since 2.18.0
 */
class PaymentGatewayRegister extends PaymentGatewaysIterator
{
    /**
     * @var string[]
     */
    protected $gateways = [];

    /**
     * Get Gateways
     *
     * @unreleased add $apiVersion filter param
     * @since 2.18.0
     */
    public function getPaymentGateways(int $apiVersion = null): array
    {
        $gateways = array_map(static function (string $gatewayClass) {
            return give($gatewayClass);
        }, $this->gateways);

        if (!$apiVersion) {
            return $gateways;
        }

        return array_filter($gateways, static function (BasePaymentGateway $gateway) use ($apiVersion) {
            return in_array($apiVersion, $gateway::supportsApiVersions(), true);
        });
    }

    /**
     * Get Gateway
     *
     * @since 2.18.0
     */
    public function getPaymentGateway(string $id): BasePaymentGateway
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
     */
    public function hasPaymentGateway(string $id): bool
    {
        return isset($this->gateways[$id]);
    }

    /**
     * Register Gateway
     *
     * @since 2.18.0
     *
     * @throws OverflowException|InvalidArgumentException|Exception
     */
    public function registerGateway(string $gatewayClass)
    {
        if (!is_subclass_of($gatewayClass, BasePaymentGateway::class)) {
            throw new InvalidArgumentException(
                sprintf(
                    '%1$s must extend %2$s',
                    $gatewayClass,
                    BasePaymentGateway::class
                )
            );
        }

        $gatewayId = $gatewayClass::id();

        if ($this->hasPaymentGateway($gatewayId)) {
            throw new OverflowException("Cannot register a gateway with an id that already exists: $gatewayId");
        }


        $this->gateways[$gatewayId] = $gatewayClass;


        $this->registerGatewayWithServiceContainer($gatewayClass, $gatewayId);
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
     * @return void
     */
    private function registerGatewayWithServiceContainer(string $gatewayClass, string $gatewayId)
    {
        give()->singleton($gatewayClass, function (Container $container) use ($gatewayClass, $gatewayId) {
            $subscriptionModule = apply_filters("givewp_gateway_{$gatewayId}_subscription_module", null);

            return new $gatewayClass($subscriptionModule ? $container->make($subscriptionModule) : null);
        });
    }
}
