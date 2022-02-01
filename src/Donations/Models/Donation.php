<?php

namespace Give\Donations\Models;

use DateTime;
use Give\Donors\Models\Donor;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Subscriptions\Models\Subscription;

/**
 * Class Donation
 *
 * @unreleased
 */
class Donation
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var int
     */
    public $sequentialId = null;
    /**
     * @var DateTime
     */
    public $createdAt;
    /**
     * @var DateTime
     */
    public $updatedAt;
    /**
     * @var string
     */
    public $status;
    /**
     * @var int
     */
    public $amount;
    /**
     * @var string
     */
    public $currency;
    /**
     * @var string
     */
    public $gateway;
    /**
     * @var int
     */
    public $donorId;
    /**
     * @var string
     */
    public $firstName;
    /**
     * @var string
     */
    public $lastName;
    /**
     * @var string
     */
    public $email;
    /**
     * @var int
     */
    public $parentId = 0;
    /**
     * @var int
     */
    public $subscriptionId = null;

    /**
     * Find donation by ID
     *
     * @unreleased
     *
     * @param  int  $id
     * @return Donation
     */
    public static function find($id)
    {
        return give()->donationRepository->getById($id);
    }

    /**
     * @unreleased
     *
     * @return Donor
     */
    public function donor()
    {
        return give()->donorRepository->getById($this->donorId);
    }

    /**
     * @unreleased
     *
     * @return Subscription
     */
    public function subscription()
    {
        if ($this->subscriptionId) {
            return give()->subscriptionRepository->getById($this->subscriptionId);
        }

        return give()->subscriptionRepository->getByDonationId($this->id);
    }

    /**
     * @param  Donation  $donation
     * @return Donation
     * @throws DatabaseQueryException
     */
    public static function create(Donation $donation)
    {
        return give()->donationRepository->insert($donation);
    }

    /**
     * @return Donation
     * @throws DatabaseQueryException
     */
    public function save()
    {
        return give()->donationRepository->update($this);
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return give()->donationRepository->getMeta($this);
    }

    /**
     * @return int
     */
    public function getSequentialId()
    {
        return give()->donationRepository->getSequentialId($this->id);
    }

}
