<?php

namespace Give\Donations\ViewModels;

use Give\Donations\Models\Donation;

/**
 * Class DonationDetailsViewModel
 *
 * @unreleased
 */
class DonationDetailsViewModel
{
    /**
     * @unreleased
     *
     * @var Donation $donation
     */
    protected $donation;

    /**
     * @unreleased
     *
     * @var int $id
     */
    public function __construct(int $id)
    {
        $this->donation = give()->donations->getById($id);
    }

    /**
     * @unreleased
     *
     * @return array
     */
    public function exports(): array
    {
        if ( ! $this->donation) {
            return [];
        }

        $donationArray = $this->donation->toArray();

        $donationArray['amount'] = [
            'currency' => $this->donation->amount->getCurrency(),
            'value' => $this->donation->amount->getAmount(),
        ];

        $donationArray['gatewayLabel'] = give_get_gateway_checkout_label($this->donation->gatewayId);

        return $donationArray;
    }
}
