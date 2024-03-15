<?php

namespace Give\EventTickets\Actions;

use Give\EventTickets\Repositories\EventTicketRepository;
use Give\Framework\Receipts\DonationReceipt;
use Give\Framework\Support\ValueObjects\Money;

/**
 * @since 3.6.0
 */
class UpdateDonationConfirmationPageReceiptDonationAmount
{
    /**
     * Subtract event tickets total from donation amount line item
     *
     * @since 3.6.0
     */
    public function __invoke(string $amount, DonationReceipt $receipt): string
    {
        $totalTicketAmount = give(EventTicketRepository::class)->getTotalByDonation($receipt->donation);

        return Money::fromDecimal($amount, $receipt->donation->amount->getCurrency())->subtract(
            $totalTicketAmount
        )->formatToDecimal();
    }

}
