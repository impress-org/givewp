<?php

namespace Give\Framework\PaymentGateways\EventHandlers;

use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Log\PaymentGatewayLog;

/**
 * @unreleased
 */
class DonationCompleted
{
    /**
     * @unreleased
     * @throws Exception
     */
    public function __invoke(string $gatewayTransactionId, string $message = '', $skipRecurringDonations = false)
    {
        $donation = give()->donations->getByGatewayTransactionId($gatewayTransactionId);

        if ( ! $donation || $donation->status->isComplete()) {
            return;
        }

        if ($skipRecurringDonations && ! $donation->type->isSingle()) {
            return;
        }

        $donation->status = DonationStatus::COMPLETE();
        $donation->save();

        if (empty($message)) {
            $message = __('Transaction Completed.', 'give');
        }

        DonationNote::create([
            'donationId' => $donation->id,
            'content' => $message . ' ' . sprintf(__('% transaction ID: %s', 'give'),
                    $donation->gateway()->getName(),
                    $donation->gatewayTransactionId
                ),
        ]);

        PaymentGatewayLog::success(
            $message . ' ' . sprintf('Donation ID: %s.', $donation->id),
            [
                'Payment Gateway' => $donation->gateway()->getId(),
                'Gateway Transaction Id' => $donation->gatewayTransactionId,
                'Donation' => $donation->id,
            ]
        );
    }
}
