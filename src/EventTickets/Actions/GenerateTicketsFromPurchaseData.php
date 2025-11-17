<?php

namespace Give\EventTickets\Actions;

use Give\Donations\Models\Donation;
use Give\EventTickets\DataTransferObjects\TicketPurchaseData;
use Give\EventTickets\Models\EventTicket;
use Give\Framework\Support\ValueObjects\Money;

/**
 * @since 3.6.0
 */
class GenerateTicketsFromPurchaseData
{
    /**
     * @since 3.6.0
     * @var Donation
     */
    protected $donation;

    /**
     * @since 3.6.0
     */
    public function __construct(Donation $donation)
    {
        $this->donation = $donation;
    }

    /**
     * @since 4.6.0 Add support for currency conversion
     * @since 3.20.0 Add "amount" to the array of props
     * @since 3.6.0
     */
    public function __invoke(TicketPurchaseData $data)
    {
        for($i = 0; $i < $data->quantity; $i++) {
            $amount = $data->ticketType->price;

            if ($this->donation->amount->getCurrency() !== $data->ticketType->price->getCurrency()) {
                $amount = new Money($data->ticketType->price->multiply($this->donation->exchangeRate)->getAmount(), $this->donation->amount->getCurrency());
            }

            EventTicket::create([
                'eventId' => $data->ticketType->eventId,
                'ticketTypeId' => $data->ticketType->id,
                'donationId' => $this->donation->id,
                'amount' => $amount,
            ]);
        }
    }
}
