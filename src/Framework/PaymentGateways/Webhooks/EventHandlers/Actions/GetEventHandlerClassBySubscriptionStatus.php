<?php

namespace Give\Framework\PaymentGateways\Webhooks\EventHandlers\Actions;

use Give\Framework\PaymentGateways\Webhooks\EventHandlers\SubscriptionActive;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\SubscriptionCancelled;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\SubscriptionCompleted;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\SubscriptionExpired;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\SubscriptionFailing;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\SubscriptionPaused;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\SubscriptionPending;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\SubscriptionSuspended;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

/**
 * @unreleased
 */
class GetEventHandlerClassBySubscriptionStatus
{
    /**
     * @unreleased
     */
    public function __invoke(SubscriptionStatus $status): string
    {
        switch ($status) {
            case $status->isActive():
                return SubscriptionActive::class;
            case $status->isCancelled():
                return SubscriptionCancelled::class;
            case $status->isCompleted():
                return SubscriptionCompleted::class;
            case $status->isExpired():
                return SubscriptionExpired::class;
            case $status->isFailing():
                return SubscriptionFailing::class;
            case $status->isPaused():
                return SubscriptionPaused::class;
            case $status->isPending():
                return SubscriptionPending::class;
            case $status->isSuspended():
                return SubscriptionSuspended::class;
            default:
                return '';
        }
    }
}
