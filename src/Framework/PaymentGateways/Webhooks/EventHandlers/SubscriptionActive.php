<?php

namespace Give\Framework\PaymentGateways\Webhooks\EventHandlers;

use Exception;
use Give\Framework\PaymentGateways\Log\PaymentGatewayLog;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\Actions\UpdateSubscriptionStatus;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

/**
 * @since 3.6.0
 */
class SubscriptionActive
{
    /**
     * @since TBD Bail when the subscription has no initial donation instead of fataling on it.
     * @since 3.6.0
     *
     * @throws Exception
     */
    public function __invoke(
        string $gatewaySubscriptionId,
        string $message = '',
        bool $initialDonationShouldBeCompleted = false
    )
    {
        $subscription = give()->subscriptions->getByGatewaySubscriptionId($gatewaySubscriptionId);

        if ( ! $subscription || $subscription->status->isActive()) {
            return;
        }

        if ($initialDonationShouldBeCompleted) {
            $initialDonation = $subscription->initialDonation();

            if ( ! $initialDonation || ! $initialDonation->status->isComplete()) {
                PaymentGatewayLog::error(
                    sprintf('The subscription was not activated for the gateway subscription ID %s because its initial donation is missing or has not completed yet.',
                        $gatewaySubscriptionId),
                    [
                        'Gateway Subscription ID' => $gatewaySubscriptionId,
                        'Message' => $message,
                    ]
                );

                return;
            }
        }

        (new UpdateSubscriptionStatus())($subscription, SubscriptionStatus::ACTIVE(), $message);
    }
}
