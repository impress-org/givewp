<?php

namespace Give\PaymentGateways\EventHandlers;

use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\PaymentGateways\Actions\UpdateDonationStatus;

/**
 * @unreleased
 */
class DonationPreapproval
{
    /**
     * @unreleased
     * @throws Exception
     */
    public function __invoke(string $gatewayTransactionId, $skipRecurringDonations = false, string $message = '')
    {
        $donation = give()->donations->getByGatewayTransactionId($gatewayTransactionId);

        if ( ! $donation || $donation->status->isPreapproval()) {
            return;
        }

        if ($skipRecurringDonations && ! $donation->type->isSingle()) {
            return;
        }

        (new UpdateDonationStatus())($donation, DonationStatus::PREAPPROVAL(), $message);
    }
}
