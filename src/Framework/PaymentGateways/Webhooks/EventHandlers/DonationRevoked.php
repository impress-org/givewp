<?php

namespace Give\Framework\PaymentGateways\Webhooks\EventHandlers;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\Actions\UpdateDonationStatus;

/**
 * @since 3.6.0
 */
class DonationRevoked
{
    /**
     * @unreleased Add $donationId to support gateways that only receive the transaction ID via webhook (e.g. PayFast).
     * @since 3.6.0
     * @throws Exception
     */
    public function __invoke(string $gatewayTransactionId, string $message = '', bool $skipRecurringDonations = false, int $donationId = 0)
    {
        if ($donationId > 0) {
            $donation = Donation::find($donationId);

            if ($donation) {
                $donation->gatewayTransactionId = $gatewayTransactionId;
                $donation->save();
            }
        } else {
            $donation = give()->donations->getByGatewayTransactionId($gatewayTransactionId);
        }

        if ( ! $donation || $donation->status->isRevoked()) {
            return;
        }

        if ($skipRecurringDonations && ! $donation->type->isSingle()) {
            return;
        }

        (new UpdateDonationStatus())($donation, DonationStatus::REVOKED(), $message);
    }
}
