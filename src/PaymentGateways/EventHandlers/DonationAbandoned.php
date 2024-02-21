<?php

namespace Give\PaymentGateways\EventHandlers;

use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\PaymentGateways\Actions\UpdateDonationStatus;

/**
 * @unreleased
 */
class DonationAbandoned
{
    /**
     * @unreleased
     * @throws Exception
     */
    public function __invoke(string $gatewayTransactionId, $skipRecurringDonations = false, string $message = '')
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
