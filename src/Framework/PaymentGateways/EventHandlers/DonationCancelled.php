<?php

namespace Give\Framework\PaymentGateways\EventHandlers;

use Give\Donations\Repositories\DonationRepository;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Exceptions\Primitives\Exception;
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
    public function __invoke(string $gatewayTransactionId, $skipRecurringDonations = false, string $message = '')
    {
        $donation = give(DonationRepository::class)->getByGatewayTransactionId($gatewayTransactionId);

        if ( ! $donation || $donation->status->isCancelled()) {
            return;
        }

        if ($skipRecurringDonations && ! $donation->type->isSingle()) {
            return;
        }

        (new UpdateDonationStatus())($donation, DonationStatus::CANCELLED(), $message);
    }
}
