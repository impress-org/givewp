<?php

namespace Give\Framework\PaymentGateways;

use Give\Donations\Models\Donation;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Contracts\PaymentGatewayInterface;
use Give\Framework\PaymentGateways\Contracts\Subscription\SubscriptionAmountEditable;
use Give\Framework\PaymentGateways\Contracts\Subscription\SubscriptionDashboardLinkable;
use Give\Framework\PaymentGateways\Contracts\Subscription\SubscriptionPaymentMethodEditable;
use Give\Framework\PaymentGateways\Contracts\Subscription\SubscriptionTransactionsSynchronizable;
use Give\Framework\Support\ValueObjects\Money;
use Give\Subscriptions\Models\Subscription;

/**
 * @since 2.18.0
 */
abstract class PaymentGateway extends BasePaymentGateway implements PaymentGatewayInterface,
                                                                    SubscriptionDashboardLinkable,
                                                                    SubscriptionAmountEditable,
                                                                    SubscriptionPaymentMethodEditable,
                                                                    SubscriptionTransactionsSynchronizable
{

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
}
