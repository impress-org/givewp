<?php

namespace Give\Framework\PaymentGateways\EventHandlers;

use Exception;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\Actions\UpdateDonationStatus;

/**
 * @unreleased
 */
class DonationAbandoned
{
    /**
     * @unreleased
     * @throws Exception
     */
    public function __invoke(string $gatewayTransactionId, string $message = '', $skipRecurringDonations = false)
    {
        $donation = give()->donations->getByGatewayTransactionId($gatewayTransactionId);

        if ( ! $donation || $donation->status->isAbandoned()) {
            return;
        }

        if ($skipRecurringDonations && ! $donation->type->isSingle()) {
            return;
        }

        (new UpdateDonationStatus())($donation, DonationStatus::ABANDONED(), $message);
    }
}
