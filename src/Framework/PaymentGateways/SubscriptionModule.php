<?php

namespace Give\Framework\PaymentGateways;

use Give\Framework\PaymentGateways\Contracts\SubscriptionModuleInterface;
use Give\Subscriptions\Models\Subscription;

/**
 * @unreleased
 */
abstract class SubscriptionModule implements SubscriptionModuleInterface
{

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function canDonorEditSubscription(Subscription $subscriptionModel)
    {
        return $subscriptionModel->gatewaySubscriptionId &&
            in_array($subscriptionModel->status->getValue(), ['active', 'failing'], true);
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function canDonorUpdateSubscriptionAmount(Subscription $subscriptionModel)
    {
        return $this->canDonorEditSubscription($subscriptionModel) &&
            method_exists($this, 'updateSubscriptionAmount');
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function canDonorUpdateSubscriptionPaymentMethod(Subscription $subscriptionModel)
    {
        return $this->canDonorEditSubscription($subscriptionModel) &&
            method_exists(
                $this,
                'updateSubscriptionPaymentMethod'
            );
    }
}
