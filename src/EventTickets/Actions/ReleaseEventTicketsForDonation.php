<?php

namespace Give\EventTickets\Actions;

use Give\Donations\Models\Donation;
use Give\EventTickets\Repositories\EventTicketRepository;

/**
 * @since TBD
 */
class ReleaseEventTicketsForDonation
{
    /**
     * @since TBD
     */
    public function __invoke(Donation $donation)
    {
        if (!$this->excludesFromSales($donation)) {
            return;
        }

        $tickets = give(EventTicketRepository::class)->queryByDonationId($donation->id)->getAll() ?? [];

        foreach ($tickets as $ticket) {
            $ticket->delete();
        }
    }

    /**
     * @since TBD
     */
    private function excludesFromSales(Donation $donation): bool
    {
        $status = $donation->status;

        return $status->isCancelled()
            || $status->isRefunded()
            || $status->isFailed()
            || $status->isAbandoned()
            || $status->isRevoked();
    }
}
