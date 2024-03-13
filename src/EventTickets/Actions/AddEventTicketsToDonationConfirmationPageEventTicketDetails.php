<?php

namespace Give\EventTickets\Actions;

use Give\EventTickets\Repositories\EventTicketRepository;
use Give\Framework\Receipts\DonationReceipt;
use Give\Framework\Receipts\Properties\ReceiptDetail;

/**
 * @since 3.6.0
 */
class AddEventTicketsToDonationConfirmationPageEventTicketDetails
{
    /**
     * @since 3.6.0
     */
    public function __invoke(DonationReceipt $receipt): void
    {
        $eventTickets = give(EventTicketRepository::class)->queryByDonationId($receipt->donation->id)->getAll();

        if (empty($eventTickets)) {
            return;
        }

        $event = $eventTickets[0]->event()->get();
        $ticketTypes = [];

        foreach ($eventTickets as $eventTicket) {
            $ticketType = $eventTicket->ticketType()->get();
            $ticketTypeId = $ticketType->id;

            if (isset($ticketTypes[$ticketTypeId])) {
                $ticketTypes[$ticketTypeId]['quantity'] += 1;
            } else {
                $ticketTypes[$ticketTypeId] = [
                    'title' => $ticketType->title,
                    'quantity' => 1,
                ];
            }
        }

        foreach ($ticketTypes as $ticketType) {
            $detailString = sprintf(__('%s - %s', 'give'), $event->title, $ticketType['title']);
            $receipt->eventTicketsDetails->addDetail(new ReceiptDetail($detailString, $ticketType['quantity']));
        }
    }
}
