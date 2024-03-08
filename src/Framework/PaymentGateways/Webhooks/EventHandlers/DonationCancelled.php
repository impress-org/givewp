<?php

namespace Give\Framework\PaymentGateways\Webhooks\EventHandlers;

use Exception;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\Actions\UpdateDonationStatus;

/**
 * @unreleased
 */
class DonationCancelled
{
    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function __invoke(string $gatewayTransactionId, string $message = '', $skipRecurringDonations = false)
    {
        $donation = give()->donations->getByGatewayTransactionId($gatewayTransactionId);

        if ( ! $donation || $donation->status->isCancelled()) {
            return;
        }

        if ($skipRecurringDonations && ! $donation->type->isSingle()) {
            return;
        }

        (new UpdateDonationStatus())($donation, DonationStatus::CANCELLED(), $message);
    }
}
