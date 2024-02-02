<?php

namespace Give\Framework\PaymentGateways\EventHandlers;

use Give\Donations\Models\DonationNote;
use Give\Donations\Repositories\DonationRepository;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Log\PaymentGatewayLog;

/**
 * @unreleased
 */
class DonationFailed
{
    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function __invoke(string $gatewayTransactionId, string $message = '', $skipRecurringDonations = false)
    {
        $donation = give(DonationRepository::class)->getByGatewayTransactionId($gatewayTransactionId);

        if ( ! $donation || $donation->status->isFailed()) {
            return;
        }

        if ($skipRecurringDonations && ! $donation->type->isSingle()) {
            return;
        }

        $donation->status = DonationStatus::FAILED();
        $donation->save();

        if (empty($message)) {
            $message = __('Transaction Failed.', 'give');
        }

        DonationNote::create([
            'donationId' => $donation->id,
            'content' => $message . ' ' . sprintf(__('%s transaction ID: %s', 'give'),
                    $donation->gateway()->getName(),
                    $donation->gatewayTransactionId
                ),
        ]);

        PaymentGatewayLog::success($message . ' ' . sprintf('Donation ID: %s.', $donation->id),
            [
                'Payment Gateway' => $donation->gateway()->getId(),
                'Gateway Transaction Id' => $donation->gatewayTransactionId,
                'Donation' => $donation->id,
            ]
        );
    }
}
