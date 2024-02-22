<?php

namespace Give\EventTickets\Actions;

use Give\Donations\Models\Donation;
use Give\EventTickets\DataTransferObjects\TicketPurchaseData;
use Give\EventTickets\Models\EventTicket;

/**
 * @unreleased
 */
class GenerateTicketsFromPurchaseData
{
    /**
     * @unreleased
     * @var Donation
     */
    protected $donation;

    /**
     * @unreleased
     */
    public function __construct(Donation $donation)
    {
        $this->donation = $donation;
    }

    /**
     * @unreleased
     */
    public function __invoke(TicketPurchaseData $data)
    {
        for($i = 0; $i < $data->quantity; $i++) {
            EventTicket::create([
                'eventId' => $data->ticketType->eventId,
                'ticketTypeId' => $data->ticketType->id,
                'donationId' => $this->donation->id,
            ]);
        }
    }
}
