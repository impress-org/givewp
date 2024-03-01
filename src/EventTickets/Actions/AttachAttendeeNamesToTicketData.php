<?php

namespace Give\EventTickets\Actions;

use Give\EventTickets\Models\EventTicket;
use Give\Framework\QueryBuilder\QueryBuilder;

/**
 * @unreleased
 */
class AttachAttendeeNamesToTicketData
{
    /**
     * @unreleased
     * @var array
     */
    protected $attendeeNameLookup;

    /**
     * @unreleased
     * @param EventTicket[] $tickets
     */
    public function __construct(array $tickets)
    {
        $this->attendeeNameLookup = array_reduce($this->getAttendeeDataForTickets($tickets), function($lookup, $data) {
            $lookup[$data->donationId] = $data->attendee;
            return $lookup;
        }, []);
    }

    /**
     * @unreleased
     */
    public function __invoke(EventTicket $ticket): array
    {
        return array_merge($ticket->toArray(), [
            'attendee' => $this->attendeeNameLookup[$ticket->donationId] ?? null,
        ]);
    }

    /**
     * This query relates donors names to tickets through donations.
     *
     * @unreleased
     *
     * @param EventTicket[] $tickets
     */
    protected function getAttendeeDataForTickets(array $tickets): array
    {
        return (new QueryBuilder)
            ->from('posts', 'Donation')
            ->select(
                ['Donation.ID', 'donationId'],
                ['Donor.name', 'attendee']
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
