<?php

namespace Give\Framework\PaymentGateways;

use Give\Donations\Models\Donation;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\LegacyPaymentGateways\Contracts\LegacyPaymentGatewayInterface;
use Give\Framework\PaymentGateways\Actions\GenerateGatewayRouteUrl;
use Give\Framework\PaymentGateways\Contracts\PaymentGatewayInterface;
use Give\Framework\PaymentGateways\Contracts\Subscription\SubscriptionAmountEditable;
use Give\Framework\PaymentGateways\Contracts\Subscription\SubscriptionDashboardLinkable;
use Give\Framework\PaymentGateways\Contracts\Subscription\SubscriptionPaymentMethodEditable;
use Give\Framework\PaymentGateways\Contracts\Subscription\SubscriptionTransactionsSynchronizable;
use Give\Framework\PaymentGateways\Routes\RouteSignature;
use Give\Framework\PaymentGateways\Traits\HandleHttpResponses;
use Give\Framework\PaymentGateways\Traits\HasRouteMethods;
use Give\Framework\Support\ValueObjects\Money;
use Give\Log\Log;
use Give\Subscriptions\Models\Subscription;
use ReflectionException;
use ReflectionMethod;

/**
 * @since 2.18.0
 */
abstract class PaymentGateway implements PaymentGatewayInterface,
                                         LegacyPaymentGatewayInterface,
                                         SubscriptionDashboardLinkable,
                                         SubscriptionAmountEditable,
                                         SubscriptionPaymentMethodEditable,
                                         SubscriptionTransactionsSynchronizable
{
    use HandleHttpResponses;
    use HasRouteMethods {
        supportsMethodRoute as protected SupportsOwnMethodRoute;
        callRouteMethod as protected CallOwnRouteMethod;
    }

    /**
     * @since 2.20.0 Change variable type to SubscriptionModule.
     * @var SubscriptionModule $subscriptionModule
     */
    public $subscriptionModule;

    /**
     * @since 2.20.0 Change first argument type to SubscriptionModule abstract class.
     * @since 2.18.0
     *
     * @param SubscriptionModule|null $subscriptionModule
     */
    public function __construct(SubscriptionModule $subscriptionModule = null)
    {
        if ($subscriptionModule !== null) {
            $subscriptionModule->setGateway($this);
        }

        $this->subscriptionModule = $subscriptionModule;
    }

    /**
     * @inheritDoc
     *
     * @since 2.29.0
     */
    public function supportsRefund(): bool
    {
        return $this->isFunctionImplementedInGatewayClass('refundDonation');
    }

    /**
     * @inheritDoc
     */
    public function supportsSubscriptions(): bool
    {
        return isset($this->subscriptionModule) || $this->isFunctionImplementedInGatewayClass('createSubscription');
    }

    /**
     * If a subscription module isn't wanted this method can be overridden by a child class instead.
     * Just make sure to override the supportsSubscriptions method as well.
     *
     * @inheritDoc
     */
    public function createSubscription(
        Donation $donation,
        Subscription $subscription,
        $gatewayData
    ) {
        return $this->subscriptionModule->createSubscription($donation, $subscription, $gatewayData);
    }

    /**
     * @inheritDoc
     */
    public function cancelSubscription(Subscription $subscription)
    {
        $this->subscriptionModule->cancelSubscription($subscription);
    }

    /**
     * @since 2.21.2
     * @inheritDoc
     */
    public function canSyncSubscriptionWithPaymentGateway(): bool
    {
        if ($this->subscriptionModule) {
            return $this->subscriptionModule->canSyncSubscriptionWithPaymentGateway();
        }

        return $this->isFunctionImplementedInGatewayClass('synchronizeSubscription');
    }

    /**
     * @since 2.21.2
     * @inheritDoc
     */
    public function canUpdateSubscriptionAmount(): bool
    {
        if ($this->subscriptionModule) {
            return $this->subscriptionModule->canUpdateSubscriptionAmount();
        }

        return $this->isFunctionImplementedInGatewayClass('updateSubscriptionAmount');
    }

    /**
     * @since 2.21.2
     * @inheritDoc
     */
    public function canUpdateSubscriptionPaymentMethod(): bool
    {
        if ($this->subscriptionModule) {
            return $this->subscriptionModule->canUpdateSubscriptionPaymentMethod();
        }

        return $this->isFunctionImplementedInGatewayClass('updateSubscriptionPaymentMethod');
    }

    /**
     * @since 2.25.0 update return logic
     * @since 2.21.2
     */
    public function hasGatewayDashboardSubscriptionUrl(): bool
    {
        if ($this->subscriptionModule) {
            return $this->subscriptionModule->hasGatewayDashboardSubscriptionUrl();
        }

        return $this->isFunctionImplementedInGatewayClass('gatewayDashboardSubscriptionUrl');
    }

    /**
     * @since 2.21.2
     * @inheritDoc
     * @throws Exception
     */
    public function synchronizeSubscription(Subscription $subscription)
    {
        if ($this->subscriptionModule instanceof SubscriptionTransactionsSynchronizable) {
            $this->subscriptionModule->synchronizeSubscription($subscription);

            return;
        }

        throw new Exception('Gateway does not support syncing subscriptions.');
    }

    /**
     * @since 2.21.2
     * @inheritDoc
     * @throws Exception
     */
    public function updateSubscriptionAmount(Subscription $subscription, Money $newRenewalAmount)
    {
        if ($this->subscriptionModule instanceof SubscriptionAmountEditable) {
            $this->subscriptionModule->updateSubscriptionAmount($subscription, $newRenewalAmount);

            return;
        }

        throw new Exception('Gateway does not support updating the subscription amount.');
    }

    /**
     * @since 2.21.2
     * @inheritDoc
     * @throws Exception
     */
    public function updateSubscriptionPaymentMethod(Subscription $subscription, $gatewayData)
    {
        if ($this->subscriptionModule instanceof SubscriptionPaymentMethodEditable) {
            $this->subscriptionModule->updateSubscriptionPaymentMethod($subscription, $gatewayData);

            return;
        }

        throw new Exception('Gateway does not support updating the subscription payment method.');
    }

    /**
     * @since 2.21.2
     * @inheritDoc
     */
    public function gatewayDashboardSubscriptionUrl(Subscription $subscription): string
    {
        if ($this->subscriptionModule instanceof SubscriptionDashboardLinkable) {
            return $this->subscriptionModule->gatewayDashboardSubscriptionUrl($subscription);
        }

        return false;
    }

    /**
     * Generate gateway route url
     *
     * @since 2.18.0
     * @since 2.19.0 remove $donationId param in favor of args
     */
    public function generateGatewayRouteUrl(string $gatewayMethod, array $args = []): string
    {
        return (new GenerateGatewayRouteUrl())(static::id(), $gatewayMethod, $args);
    }

    /**
     * Generate secure gateway route url
     *
     * @since 2.19.5 replace nonce with hash and expiration
     * @since 2.19.4 replace RouteSignature args with unique donationId
     * @since 2.19.0
     */
    public function generateSecureGatewayRouteUrl(string $gatewayMethod, int $donationId, array $args = []): string
    {
        $signature = new RouteSignature(static::id(), $gatewayMethod, $donationId);

        return (new GenerateGatewayRouteUrl())(
            static::id(),
            $gatewayMethod,
            array_merge($args, [
                'give-route-signature' => $signature->toHash(),
                'give-route-signature-id' => $donationId,
                'give-route-signature-expiration' => $signature->expiration,
            ])
        );
    }

    /**
     * @since 2.20.0
     */
    public function supportsMethodRoute(string $method): bool
    {
        if ($this->subscriptionModule && $this->subscriptionModule->supportsMethodRoute($method)) {
            return true;
        }

        return $this->supportsOwnMethodRoute($method);
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
        if ($this->subscriptionModule && $this->subscriptionModule->supportsMethodRoute($method)) {
            return $this->subscriptionModule->callRouteMethod($method, $queryParams);
        }

        return $this->callOwnRouteMethod($method, $queryParams);
    }

    /**
     * Checks to see if the provided method is being used by the child gateway class. This is used as a helper in the "can" methods
     * to see if the gateway is implementing a recurring feature without using a subscription module.
     *
     * @since 2.21.2
     */
    private function isFunctionImplementedInGatewayClass(string $methodName): bool
    {
        try {
            $reflector = new ReflectionMethod($this, $methodName);
        } catch (ReflectionException $e) {
            Log::error(
                sprintf(
                    'ReflectionException thrown when trying to check if %s::%s is implemented in the gateway class.',
                    static::id(),
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
