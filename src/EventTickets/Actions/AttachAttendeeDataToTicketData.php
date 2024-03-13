<?php

namespace Give\EventTickets\Actions;

use Give\EventTickets\Models\EventTicket;
use Give\Framework\QueryBuilder\QueryBuilder;

/**
 * @since 3.6.0
 */
class AttachAttendeeDataToTicketData
{
    /**
     * @since 3.6.0
     * @var array
     */
    protected $attendeeDataLookup;

    /**
     * @since 3.6.0
     * @param EventTicket[] $tickets
     */
    public function __construct(array $tickets)
    {
        $this->attendeeDataLookup = array_reduce($this->getAttendeeDataForTickets($tickets), function ($lookup, $data) {
            $lookup[$data->donationId] = ['name' => $data->attendeeName, 'email' => $data->attendeeEmail];
            return $lookup;
        }, []);
    }

    /**
     * @since 3.6.0
     */
    public function __invoke(EventTicket $ticket): array
    {
        return array_merge($ticket->toArray(), [
            'attendee' => $this->attendeeDataLookup[$ticket->donationId] ?? null,
        ]);
    }

    /**
     * This query relates donors names to tickets through donations.
     *
     * @since 3.6.0
     *
     * @param EventTicket[] $tickets
     */
    protected function getAttendeeDataForTickets(array $tickets): array
    {
        if (empty($tickets)) {
            return [];
        }

        return (new QueryBuilder)
            ->from('posts', 'Donation')
            ->select(
                ['Donation.ID', 'donationId'],
                ['Donor.name', 'attendeeName'],
                ['Donor.email', 'attendeeEmail']
            )
            ->join(function($builder) {
                $builder
                    ->leftJoin('give_donationmeta', $tableAlias = 'DonationDonorId' )
                    ->on('Donation.ID', "DonationDonorId.donation_id")
                    ->andOn("DonationDonorId.meta_key", '_give_payment_donor_id', true);
            })
            ->join(function($builder) {
                $builder
                    ->leftJoin('give_donors', $tableAlias = 'Donor' )
                    ->on('DonationDonorId.meta_value', "Donor.id");
            })
            ->where('post_type', 'give_payment')
            ->whereIn('Donation.ID', array_column($tickets, 'donationId'))
            ->getAll();
    }
}
