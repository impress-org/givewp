<?php

namespace Unit\Framework\PaymentGateways\Webhooks\EventHandlers\Actions;

use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\Actions\GetEventHandlerClassByDonationStatus;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\DonationAbandoned;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\DonationCancelled;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\DonationCompleted;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\DonationFailed;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\DonationPending;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\DonationPreapproval;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\DonationProcessing;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\DonationRefunded;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\DonationRevoked;
use Give\Tests\TestCase;

/**
 * @unreleased
 */
class GetEventHandlerClassByDonationStatusTest extends TestCase
{
    /**
     * @unreleased
     */
    public function testShouldReturnDonationStatusEventHandlerClass()
    {
        foreach (DonationStatus::values() as $status) {
            if ($eventHandlerClass = (new GetEventHandlerClassByDonationStatus())($status)) {
                switch ($status) {
                    case $status->isAbandoned():
                        $this->assertEquals(DonationAbandoned::class, $eventHandlerClass);
                        break;
                    case $status->isCancelled():
                        $this->assertEquals(DonationCancelled::class, $eventHandlerClass);
                        break;
                    case $status->isComplete():
                        $this->assertEquals(DonationCompleted::class, $eventHandlerClass);
                        break;
                    case $status->isFailed():
                        $this->assertEquals(DonationFailed::class, $eventHandlerClass);
                        break;
                    case $status->isPending():
                        $this->assertEquals(DonationPending::class, $eventHandlerClass);
                        break;
                    case $status->isPreapproval():
                        $this->assertEquals(DonationPreapproval::class, $eventHandlerClass);
                        break;
                    case $status->isProcessing():
                        $this->assertEquals(DonationProcessing::class, $eventHandlerClass);
                        break;
                    case $status->isRefunded():
                        $this->assertEquals(DonationRefunded::class, $eventHandlerClass);
                        break;
                    case $status->isRevoked():
                        $this->assertEquals(DonationRevoked::class, $eventHandlerClass);
                        break;
                }
            }
        }
    }
}
