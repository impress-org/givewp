<?php

namespace Give\Framework\PaymentGateways\Traits;

trait HasSubscriptionModule
{
     /**
     * @unreleased
     */
    public function supportsSubscriptions(): bool
    {
        return isset($this->subscriptionModule);
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
}