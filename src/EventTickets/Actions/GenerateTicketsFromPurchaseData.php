<?php

namespace Give\EventTickets\Actions;

use Give\Donations\Models\Donation;
use Give\EventTickets\DataTransferObjects\TicketPurchaseData;
use Give\EventTickets\Models\EventTicket;

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
     * @since 3.20.0 Add "amount" to the array of props
     * @since 3.6.0
     */
    public function __invoke(TicketPurchaseData $data)
    {
        for($i = 0; $i < $data->quantity; $i++) {
            EventTicket::create([
                'eventId' => $data->ticketType->eventId,
                'ticketTypeId' => $data->ticketType->id,
                'donationId' => $this->donation->id,
                'amount' => $data->ticketType->price,
            ]);
        }
    }
}
