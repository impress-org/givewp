<?php

namespace Give\Framework\PaymentGateways;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Actions\GenerateGatewayRouteUrl;
use Give\Framework\PaymentGateways\Contracts\BasePaymentGatewayInterface;
use Give\Framework\PaymentGateways\Contracts\PaymentGatewayInterface;
use Give\Framework\PaymentGateways\Contracts\PaymentGatewayInterfaceV3;
use Give\Framework\PaymentGateways\Routes\RouteSignature;
use Give\Framework\PaymentGateways\Traits\HandleHttpResponses;
use Give\Framework\PaymentGateways\Traits\HasRouteMethods;
use Give\Framework\PaymentGateways\Traits\HasSubscriptionModule;
use Give\Log\Log;
use ReflectionException;
use ReflectionMethod;

/**
 * @unreleased
 */
abstract class BasePaymentGateway implements BasePaymentGatewayInterface
{
    use HasSubscriptionModule;
    use HandleHttpResponses;
    use HasRouteMethods {
        supportsMethodRoute as protected SupportsOwnMethodRoute;
        callRouteMethod as protected CallOwnRouteMethod;
    }

    /**
     * @unreleased
     */
    public static function supportsApiVersions(): array
    {
        $versions = [];

        if (is_a(static::class, PaymentGatewayInterface::class, true)) {
            $versions[] = PaymentGatewayInterface::API_VERSION;
        }

        if (is_a(static::class, PaymentGatewayInterfaceV3::class, true)) {
            $versions[] = PaymentGatewayInterfaceV3::API_VERSION;
        }

        return $versions;
    }

    /**
     * @unreleased
     *
     * @var SubscriptionModule $subscriptionModule
     */
    public $subscriptionModule;

    /**
     * @unreleased
     *
     * @param  SubscriptionModule|null  $subscriptionModule
     */
    public function __construct(SubscriptionModule $subscriptionModule = null)
    {
        if ($subscriptionModule !== null) {
            $subscriptionModule->setGateway($this);
        }

        $this->subscriptionModule = $subscriptionModule;
    }

    /**
     * @since 2.29.0
     */
    public function supportsRefund(): bool
    {
        return $this->isFunctionImplementedInGatewayClass('refundDonation');
    }

    /**
     * Generate gateway route url
     *
     * @unreleased
     */
    public function generateGatewayRouteUrl(string $gatewayMethod, array $args = []): string
    {
        return (new GenerateGatewayRouteUrl())($this::id(), $gatewayMethod, $args);
    }

    /**
     * Generate secure gateway route url
     *
     * @unreleased
     */
    public function generateSecureGatewayRouteUrl(string $gatewayMethod, int $donationId, array $args = []): string
    {
        $signature = new RouteSignature($this::id(), $gatewayMethod, $donationId);

        return (new GenerateGatewayRouteUrl())(
            $this::id(),
            $gatewayMethod,
            array_merge($args, [
                'give-route-signature' => $signature->toHash(),
                'give-route-signature-id' => $donationId,
                'give-route-signature-expiration' => $signature->expiration,
            ])
        );
    }

    /**
     * @unreleased
     */
    public function supportsMethodRoute(string $method): bool
    {
        if ($this->subscriptionModule && $this->subscriptionModule->supportsMethodRoute($method)) {
            return true;
        }

        return $this->supportsOwnMethodRoute($method);
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
        if ($this->subscriptionModule && $this->subscriptionModule->supportsMethodRoute($method)) {
            return $this->subscriptionModule->callRouteMethod($method, $queryParams);
        }

        return $this->callOwnRouteMethod($method, $queryParams);
    }

    /**
     * Checks to see if the provided method is being used by the child gateway class. This is used as a helper in the "can" methods
     * to see if the gateway is implementing a recurring feature without using a subscription module.
     *
     * @unreleased 2
     */
    protected function isFunctionImplementedInGatewayClass(string $methodName): bool
    {
        try {
            $reflector = new ReflectionMethod($this, $methodName);
        } catch (ReflectionException $e) {
            Log::error(
                sprintf(
                    'ReflectionException thrown when trying to check if %s::%s is implemented in the gateway class.',
                    $this::id(),
                    $methodName
                ),
                [
                    'exception' => $e,
                ]
            );
            return false;
        }

        return ($reflector->getDeclaringClass()->getName() === get_class($this));
    }
}
