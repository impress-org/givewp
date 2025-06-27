<?php

namespace Give\Framework\PaymentGateways\Webhooks\EventHandlers\Actions;

use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\DonationAbandoned;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\DonationCancelled;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\DonationCompleted;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\DonationFailed;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\DonationPending;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\DonationPreapproval;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\DonationProcessing;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\DonationRefunded;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\DonationRevoked;

/**
 * @unreleased
 */
class GetEventHandlerClassByDonationStatus
{
    /**
     * @unreleased
     */
    public function __invoke(DonationStatus $status): string
    {
        switch ($status) {
            case $status->isAbandoned():
                return DonationAbandoned::class;
            case $status->isCancelled():
                return DonationCancelled::class;
            case $status->isComplete():
                return DonationCompleted::class;
            case $status->isFailed():
                return DonationFailed::class;
            case $status->isPending():
                return DonationPending::class;
            case $status->isPreapproval():
                return DonationPreapproval::class;
            case $status->isProcessing():
                return DonationProcessing::class;
            case $status->isRefunded():
                return DonationRefunded::class;
            case $status->isRevoked():
                return DonationRevoked::class;
            default:
                return '';
        }
    }
}
