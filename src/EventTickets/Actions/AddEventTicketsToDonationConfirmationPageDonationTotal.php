<?php

namespace Give\EventTickets\Actions;

use Give\EventTickets\Models\EventTicket;
use Give\EventTickets\Repositories\EventTicketRepository;
use Give\Framework\Receipts\DonationReceipt;
use Give\Framework\Receipts\Properties\ReceiptDetail;
use Give\Framework\Support\ValueObjects\Money;

/**
 * @since 3.6.0
 */
class AddEventTicketsToDonationConfirmationPageDonationTotal
{
    /**
     * @since 3.6.0
     */
    public function __invoke(DonationReceipt $receipt): void
    {
        $eventTickets = give(EventTicketRepository::class)->queryByDonationId($receipt->donation->id)->getAll();

        if (!empty($eventTickets)) {
            $currency = $receipt->donation->amount->getCurrency();
            $total = array_reduce($eventTickets, function (Money $carry, EventTicket $eventTicket) {
                $ticketType = $eventTicket->ticketType()->get();

                return $carry->add(
                    $ticketType->price
                );
            }, new Money(0, $currency));

            $receipt->donationDetails->addDetail(
                new ReceiptDetail(
                    __('Event Tickets', 'give'),
                    $total->formatToLocale()
                )
            );
        }
    }
}
