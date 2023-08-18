<?php

namespace Give\PaymentGateways\Gateways\PayPalCommerce;

use Give\Donations\Models\Donation;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\SubscriptionProcessing;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\SubscriptionModule;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use GiveRecurring\PaymentGateways\PayPalCommerce\Repositories\Subscription as SubscriptionRepository;

class PayPalCommerceSubscriptionModule extends SubscriptionModule
{
    /**
     * @since 3.0.0
     */
    public function createSubscription(
        Donation $donation,
        Subscription $subscription,
        $gatewayData = null
    ): GatewayCommand {

        $subscriptionId = $gatewayData['payPalSubscriptionId'];

        return new SubscriptionProcessing($subscriptionId);
    }

    public function cancelSubscription(Subscription $subscription)
    {
        try {
            // @phpstan-ignore-next-line
            give(SubscriptionRepository::class)
                ->updateStatus($subscription->gatewaySubscriptionId, 'cancel');

            $subscription->status = SubscriptionStatus::CANCELLED();
            $subscription->save();
        } catch (\Exception $exception) {
            throw new PaymentGatewayException(
                sprintf('Unable to cancel subscription with PayPal. %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }
}
