<?php

namespace Give\PaymentGateways\EventHandlers;

use Give\Donations\Repositories\DonationRepository;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\PaymentGateways\Actions\UpdateDonationStatus;

/**
 * @unreleased
 */
class DonationRefunded
{
    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function __invoke(string $gatewayTransactionId, $skipRecurringDonations = false, string $message = '')
    {
        $donation = give(DonationRepository::class)->getByGatewayTransactionId($gatewayTransactionId);

        if ( ! $donation || $donation->status->isRefunded()) {
            return;
        }

        if ($skipRecurringDonations && ! $donation->type->isSingle()) {
            return;
        }

        (new UpdateDonationStatus())($donation, DonationStatus::REFUNDED(), $message);
    }
}
