<?php

namespace Give\Framework\PaymentGateways\EventHandlers;

use Exception;
use Give\Donations\Repositories\DonationRepository;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\Actions\UpdateDonationStatus;

/**
 * @unreleased
 */
class DonationPending
{
    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function __invoke(string $gatewayTransactionId, string $message = '', $skipRecurringDonations = false)
    {
        $donation = give(DonationRepository::class)->getByGatewayTransactionId($gatewayTransactionId);

        if ( ! $donation || $donation->status->isPending()) {
            return;
        }

        if ($skipRecurringDonations && ! $donation->type->isSingle()) {
            return;
        }

        (new UpdateDonationStatus())($donation, DonationStatus::PENDING(), $message);
    }
}
