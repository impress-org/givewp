<?php

namespace Give\EventTickets\Actions;

use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
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
 * @since 4.16.8.1
 */
class ReleaseEventTicketsForDonation
{
    /**
     * @since 4.16.8.1
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

            // The ticket row is gone after delete(), so the note carries its identifying details
            // itself rather than a ticket ID nothing will resolve afterward.
            DonationNote::create([
                'donationId' => $donation->id,
                'content' => sprintf(
                    /* translators: 1: event ID, 2: ticket type ID, 3: donation status */
                    __('Event ticket released (event #%1$d, ticket type #%2$d) because the donation status changed to "%3$s".', 'give'),
                    $ticket->eventId,
                    $ticket->ticketTypeId,
                    $donation->status->label()
                ),
            ]);
        }
    }

    /**
     * @since 4.16.8.1
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
