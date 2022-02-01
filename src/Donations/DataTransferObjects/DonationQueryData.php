<?php

namespace Give\Donations\DataTransferObjects;

use Give\Donations\Models\Donation;

/**
 * Class DonationData
 *
 * @unreleased
 */
class DonationQueryData
{
    /**
     * @var object
     */
    private $donation;

    /**
     * Convert data from object to Donation
     *
     * @unreleased
     *
     * @return self
     */
    public static function fromObject($donation)
    {
        $self = new static();

        $self->donation = $donation;

        return $self;
    }

    /**
     * Convert DTO to Donation
     *
     * @return Donation
     */
    public function toDonation()
    {
        $donation = new Donation();

        $donation->id = $this->donation->id;
        $donation->createdAt = $this->donation->createdAt;
        $donation->updatedAt = $this->donation->updatedAt;
        $donation->status = $this->donation->status;
        $donation->amount = $this->donation->amount;
        $donation->currency = $this->donation->currency;
        $donation->gateway = $this->donation->gateway;
        $donation->donorId = (int)$this->donation->donorId;
        $donation->firstName = $this->donation->firstName;
        $donation->lastName = $this->donation->lastName;
        $donation->email = $this->donation->email;
        $donation->parentId = isset($this->donation->parentId) ? (int)$this->donation->parentId : 0;
        $donation->subscriptionId = isset($this->donation->subscriptionId) ? (int)$this->donation->subscriptionId : null;

        return $donation;
    }
}
