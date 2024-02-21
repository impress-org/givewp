<?php

namespace Give\PaymentGateways\Actions;

use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Log\PaymentGatewayLog;

/**
 * @unreleased
 */
class UpdateDonationStatus
{
    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function __invoke(
        Donation $donation,
        DonationStatus $status,
        string $gatewayTransactionId,
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
                'Gateway Transaction Id' => $gatewayTransactionId,
            ]
        );
    }

    /**
     * @unreleased
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
