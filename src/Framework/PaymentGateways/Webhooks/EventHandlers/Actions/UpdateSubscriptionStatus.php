<?php

namespace Give\Framework\PaymentGateways\Webhooks\EventHandlers\Actions;

use Exception;
use Give\Framework\PaymentGateways\Log\PaymentGatewayLog;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

/**
 * @since 3.6.0
 */
class UpdateSubscriptionStatus
{
    /**
     * @since 3.6.0
     *
     * @throws Exception
     */
    public function __invoke(
        Subscription $subscription,
        SubscriptionStatus $status,
        string $message = ''
    ) {
        $subscription->status = $status;
        $subscription->save();

        if (empty($message)) {
            $message = $this->getMessageFromStatus($status);
        }

        PaymentGatewayLog::info(
            $message . ' ' . sprintf('Subscription ID: %s.', $subscription->id),
            [
                'Payment Gateway' => $subscription->gatewayId,
                'Gateway Subscription Id' => $subscription->gatewaySubscriptionId,
                'Subscription ID' => $subscription->id,
            ]
        );
    }


    /**
     * @since 3.6.0
     */
    protected function getMessageFromStatus(SubscriptionStatus $status): string
    {
        $message = '';

        switch (true):
            case ($status->isCompleted()):
                $message = __('Subscription Completed.', 'give');
                break;
            case ($status->isExpired()):
                $message = __('Subscription Expired.', 'give');
                break;
            case ($status->isActive()):
                $message = __('Subscription Active.', 'give');
                break;
            case ($status->isCancelled()):
                $message = __('Subscription Cancelled.', 'give');
                break;
            case ($status->isFailing()):
                $message = __('Subscription Failing.', 'give');
                break;
            case ($status->isSuspended()):
                $message = __('Subscription Suspended.', 'give');
                break;
        endswitch;

        return $message;
    }
}
