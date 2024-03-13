<?php

namespace Give\Framework\PaymentGateways\Webhooks\EventHandlers\Actions;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\Log\PaymentGatewayLog;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

/**
 * @since 3.6.0
 */
class UpdateDonationStatus
{
    /**
     * @since 3.6.0
     *
     * @throws Exception
     */
    public function __invoke(
        Donation $donation,
        DonationStatus $status,
        string $message = ''
    ) {
        $donation->status = $status;
        $donation->save();

        if (empty($message)) {
            $message = $this->getMessageFromStatus($status);
        }

        DonationNote::create([
            'donationId' => $donation->id,
            'content' => $message . ' ' . sprintf(__('%s transaction ID: %s', 'give'),
                    $donation->gateway()->getName(),
                    $donation->gatewayTransactionId
                ),
        ]);

        PaymentGatewayLog::info(
            $message . ' ' . sprintf('Donation ID: %s.', $donation->id),
            [
                'Payment Gateway' => $donation->gatewayId,
                'Gateway Transaction Id' => $donation->gatewayTransactionId,
                'Donation' => $donation->id,
                'Subscription' => $donation->subscriptionId,
                'Gateway Subscription Id' => ! $donation->type->isSingle() ? $donation->subscription->gatewaySubscriptionId : null,
            ]
        );

        if ($this->isInvalidSubscriptionFirstPayment($donation) &&
            ! $donation->subscription->status->isCancelled()) {
            $donation->subscription->status = SubscriptionStatus::CANCELLED();
            $donation->subscription->save();

            PaymentGatewayLog::info(
                sprintf("The subscription was canceled because the first payment wasn't finished. Subscription ID: %s.",
                    $donation->subscription->id),
                [
                    'Payment Gateway' => $donation->gatewayId,
                    'First Payment Status' => $donation->status->getValue(),
                    'First Payment ID' => $donation->id,
                    'First Payment Gateway Transaction ID' => $donation->gatewayTransactionId,
                    'Gateway Subscription Id' => $donation->subscription->gatewaySubscriptionId,
                    'Subscription ID' => $donation->subscription->id,
                ]
            );
        }
    }

    /**
     * @since 3.6.0
     */
    private function isInvalidSubscriptionFirstPayment(Donation $donation): bool
    {
        return $donation->type->isSubscription() &&
               ($donation->status->isAbandoned() || $donation->status->isCancelled());
    }

    /**
     * @since 3.6.0
     */
    protected function getMessageFromStatus(DonationStatus $status): string
    {
        $message = '';

        switch (true):
            case ($status->isAbandoned()):
                $message = __('Donation Abandoned.', 'give');
                break;
            case ($status->isCancelled()):
                $message = __('Donation Cancelled.', 'give');
                break;
            case ($status->isComplete()):
                $message = __('Donation Completed.', 'give');
                break;
            case ($status->isFailed()):
                $message = __('Donation Failed.', 'give');
                break;
            case ($status->isPending()):
                $message = __('Donation Pending.', 'give');
                break;
            case ($status->isPreapproval()):
                $message = __('Donation Pre Approval.', 'give');
                break;
            case ($status->isProcessing()):
                $message = __('Donation Processing.', 'give');
                break;
            case ($status->isRefunded()):
                $message = __('Donation Refunded.', 'give');
                break;
            case ($status->isRevoked()):
                $message = __('Donation Revoked.', 'give');
                break;
        endswitch;

        return $message;
    }
}
