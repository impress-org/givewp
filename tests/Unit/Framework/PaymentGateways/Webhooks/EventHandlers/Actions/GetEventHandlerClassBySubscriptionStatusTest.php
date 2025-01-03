<?php

namespace Unit\Framework\PaymentGateways\Webhooks\EventHandlers\Actions;

use Give\Framework\PaymentGateways\Webhooks\EventHandlers\Actions\GetEventHandlerClassBySubscriptionStatus;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\SubscriptionActive;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\SubscriptionCancelled;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\SubscriptionCompleted;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\SubscriptionExpired;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\SubscriptionFailing;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\SubscriptionPaused;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\SubscriptionPending;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\SubscriptionSuspended;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\TestCase;

/**
 * @unreleased
 */
class GetEventHandlerClassBySubscriptionStatusTest extends TestCase
{
    /**
     * @unreleased
     */
    public function testShouldReturnSubscriptionStatusEventHandlerClass()
    {
        foreach (SubscriptionStatus::values() as $status) {
            if ($eventHandlerClass = (new GetEventHandlerClassBySubscriptionStatus())($status)) {
                switch ($status) {
                    case $status->isActive():
                        $this->assertEquals(SubscriptionActive::class, $eventHandlerClass);
                        break;
                    case $status->isCancelled():
                        $this->assertEquals(SubscriptionCancelled::class, $eventHandlerClass);
                        break;
                    case $status->isCompleted():
                        $this->assertEquals(SubscriptionCompleted::class, $eventHandlerClass);
                        break;
                    case $status->isExpired():
                        $this->assertEquals(SubscriptionExpired::class, $eventHandlerClass);
                        break;
                    case $status->isFailing():
                        $this->assertEquals(SubscriptionFailing::class, $eventHandlerClass);
                        break;
                    case $status->isPaused():
                        $this->assertEquals(SubscriptionPaused::class, $eventHandlerClass);
                        break;
                    case $status->isPending():
                        $this->assertEquals(SubscriptionPending::class, $eventHandlerClass);
                        break;
                    case $status->isSuspended():
                        $this->assertEquals(SubscriptionSuspended::class, $eventHandlerClass);
                        break;
                }
            }
        }
    }
}
