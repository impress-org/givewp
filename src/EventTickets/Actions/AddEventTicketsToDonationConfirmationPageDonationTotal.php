<?php

namespace Give\EventTickets\Actions;

use Give\EventTickets\Repositories\EventTicketRepository;
use Give\Framework\Receipts\DonationReceipt;
use Give\Framework\Receipts\Properties\ReceiptDetail;

/**
 * @since 3.6.0
 */
class AddEventTicketsToDonationConfirmationPageDonationTotal
{
    /**
     * @since 3.20.0 Refactored to use getTotalByDonation method
     * @since 3.6.0
     */
    public function __invoke(DonationReceipt $receipt): void
    {
        $totalTicketAmount = give(EventTicketRepository::class)->getTotalByDonation($receipt->donation);

        if ($totalTicketAmount->getAmount() > 0) {
            $receipt->donationDetails->addDetail(
                new ReceiptDetail(
                    __('Event Tickets', 'give'),
                    $totalTicketAmount->formatToLocale()
                )
            );
        }
    }
}
