<?php

namespace Give\Donations\Models;

use Give\Donations\Repositories\DonationRepository;
use Give\Donors\Models\Donor;
use Give\Donors\Repositories\DonorRepository;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\Repositories\SubscriptionRepository;

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
    public $sequentialId;
    /**
     * @var string
     */
    public $createdAt;
    /**
     * @var string
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
    public $parentId;
    /**
     * @var int
     */
    public $subscriptionId;

    /**
     * Find donation by ID
     *
     * @unreleased
     *
     * @param  int  $id
     * @return Donation
     */
    public function find($id)
    {
        return give(DonationRepository::class)->getById($id);
    }

    /**
     * @return Donor
     */
    public function donor()
    {
        return give(DonorRepository::class)->getById($this->donorId);
    }

    /**
     * @return Subscription
     */
    public function subscription()
    {
        if ($this->subscriptionId) {
            return give(SubscriptionRepository::class)->getById($this->subscriptionId);
        }

        return give(SubscriptionRepository::class)->getByDonationId($this->id);
    }

}
