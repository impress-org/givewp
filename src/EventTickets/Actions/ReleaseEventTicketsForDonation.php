<?php

namespace Give\EventTickets\Actions;

use Give\Donations\Models\Donation;
use Give\EventTickets\Repositories\EventTicketRepository;

/**
 * A minted event ticket is a paid perk granted on the assumption its donation succeeds. Without
 * this, a donor could purchase a ticket and then have the donation cancelled/refunded/failed and
 * still keep the ticket — access granted without a completed payment behind it. This deletes any
 * tickets tied to a donation whose status means that payment didn't happen (or was reversed), so
 * the perk is revoked along with it.
 *
 * Runs on every donation status change (see ServiceProvider's givewp_donation_updated listener)
 * and is a no-op unless the new status is one of excludesFromSales().
 *
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

        // Each delete() runs in its own transaction (matches the per-row transaction style elsewhere
        // in EventTicketRepository); a mid-loop failure can leave a release partially applied.
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
