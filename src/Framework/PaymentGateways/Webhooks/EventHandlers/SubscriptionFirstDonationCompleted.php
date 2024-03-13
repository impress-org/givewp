<?php

namespace Give\Framework\PaymentGateways\Webhooks\EventHandlers;

use Exception;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\Log\PaymentGatewayLog;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\Actions\UpdateDonationStatus;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\Actions\UpdateSubscriptionStatus;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

/**
 * @since 3.6.0
 */
class SubscriptionFirstDonationCompleted
{
    /**
     * @since 3.6.0
     */
    public function __invoke(string $gatewayTransactionId, string $message = '', bool $setSubscriptionActive = true)
    {
        $donation = give()->donations->getByGatewayTransactionId($gatewayTransactionId);

        if ( ! $donation || ! $donation->type->isSubscription() || $donation->id !== $donation->subscription->initialDonation()->id) {
            return;
        }

        try {
            if ( ! $donation->status->isComplete()) {
                if (empty($message)) {
                    $message = __('Subscription First Donation Completed.', 'give');
                }

                (new UpdateDonationStatus())($donation, DonationStatus::COMPLETE(), $message);
            }

            if ($setSubscriptionActive && ! $donation->subscription->status->isActive()) {
                (new UpdateSubscriptionStatus())($donation->subscription, SubscriptionStatus::ACTIVE(),
                    __('Subscription Active After The First Donation Got Completed.', 'give'));
            }
        } catch (Exception $e) {
            PaymentGatewayLog::error(
                sprintf('Subscription First Donation Failed! Error: %s',
                    $e->getCode() . ' - ' . $e->getMessage()),
                [
                    'Donation Id' => $donation->id,
                    'Gateway Transaction Id' => $gatewayTransactionId,
                    'Gateway Subscription Id' => $donation->subscriptionId,
                    'message' => $message,
                ]
            );
        }
    }
}
