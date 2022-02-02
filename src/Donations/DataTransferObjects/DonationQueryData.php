<?php

namespace Give\Donations\DataTransferObjects;

use DateTime;
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
     * @var int
     */
    private $amount;
    /**
     * @var string
     */
    private $currency;
    /**
     * @var int
     */
    private $donorId;
    /**
     * @var string
     */
    private $firstName;
    /**
     * @var string
     */
    private $lastName;
    /**
     * @var string
     */
    private $email;
    /**
     * @var int
     */
    private $id;
    /**
     * @var DonationStatus
     */
    private $status;
    /**
     * @var int
     */
    private $parentId;
    /**
     * @var int
     */
    private $subscriptionId;
    /**
     * @var DateTime
     */
    private $updatedAt;
    /**
     * @var DateTime
     */
    private $createdAt;
    /**
     * @var string
     */
    private $gateway;

    /**
     * Convert data from object to Donation
     *
     * @param  object  $donationQueryObject
     *
     * @unreleased
     *
     * @return self
     */
    public static function fromObject($donationQueryObject)
    {
        $self = new static();

        $self->amount = (int)$donationQueryObject->amount;
        $self->currency = $donationQueryObject->currency;
        $self->donorId = (int)$donationQueryObject->donorId;
        $self->firstName = $donationQueryObject->firstName;
        $self->lastName = $donationQueryObject->lastName;
        $self->email = $donationQueryObject->email;
        $self->id = (int)$donationQueryObject->id;
        $self->gateway = $donationQueryObject->gateway;
        $self->createdAt = $self->toDateTime($donationQueryObject->createdAt);
        $self->updatedAt = $self->toDateTime($donationQueryObject->updatedAt);
        $self->status = new DonationStatus($donationQueryObject->status);
        $self->parentId = (int)$donationQueryObject->parentId;
        $self->subscriptionId = (int)$donationQueryObject->subscriptionId;

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
            $this->amount,
            $this->currency,
            $this->donorId,
            $this->firstName,
            $this->lastName,
            $this->email
        );

        $donation->id = $this->id;
        $donation->createdAt = $this->createdAt;
        $donation->updatedAt = $this->updatedAt;
        $donation->status = $this->status;
        $donation->gateway = $this->gateway;
        $donation->parentId = $this->parentId ?: 0;
        $donation->subscriptionId = $this->subscriptionId ?: null;

        return $donation;
    }

    /**
     * @param  string  $date
     * @return DateTime
     */
    private function toDateTime($date)
    {
        $timezone = wp_timezone();

        return date_create_from_format('Y-m-d H:i:s', $date, $timezone)->setTimezone($timezone);
    }
}
