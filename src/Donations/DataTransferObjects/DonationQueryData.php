<?php

namespace Give\Donations\DataTransferObjects;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;

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
     * @param  object  $donation
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
        $donation = new Donation(
            $this->donation->amount,
            $this->donation->currency,
            (int)$this->donation->donorId,
            $this->donation->firstName,
            $this->donation->lastName,
            $this->donation->email
        );

        $donation->id = $this->donation->id;
        $donation->createdAt = $this->donation->createdAt;
        $donation->updatedAt = $this->donation->updatedAt;
        $donation->status = new DonationStatus($this->donation->status);
        $donation->gateway = $this->donation->gateway;
        $donation->parentId = isset($this->donation->parentId) ? (int)$this->donation->parentId : 0;
        $donation->subscriptionId = isset($this->donation->subscriptionId) ? (int)$this->donation->subscriptionId : null;

        return $donation;
    }
}
