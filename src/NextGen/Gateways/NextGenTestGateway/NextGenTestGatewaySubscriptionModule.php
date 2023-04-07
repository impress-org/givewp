<?php

namespace Give\NextGen\Gateways\NextGenTestGateway;

use Exception;
use Give\Donations\Models\Donation;
use Give\Framework\PaymentGateways\Commands\SubscriptionComplete;
use Give\Framework\PaymentGateways\Contracts\Subscription\SubscriptionAmountEditable;
use Give\Framework\PaymentGateways\SubscriptionModule;
use Give\Framework\Support\ValueObjects\Money;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

class NextGenTestGatewaySubscriptionModule extends SubscriptionModule implements SubscriptionAmountEditable
{
    /**
     * @since 0.3.0
     */
    public function createSubscription(
        Donation $donation,
        Subscription $subscription,
        $gatewayData = null
    ): SubscriptionComplete {
        return new SubscriptionComplete(
            "test-gateway-transaction-id-$donation->id",
            "test-gateway-subscription-id-$subscription->id"
        );
    }


    /**
     * @since 0.3.0
     *
     * @throws Exception
     */
    public function cancelSubscription(Subscription $subscription)
    {
        $subscription->status = SubscriptionStatus::CANCELLED();
        $subscription->save();
    }

    /**
     * @since 0.3.0
     * 
     * @throws Exception
     */
    public function updateSubscriptionAmount(Subscription $subscription, Money $newRenewalAmount)
    {
        $subscription->amount = $newRenewalAmount;
        $subscription->save();
    }
}