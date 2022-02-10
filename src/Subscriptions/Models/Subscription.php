<?php

namespace Give\Subscriptions\Models;

use Give\Donations\Models\Donation;
use Give\Donors\Models\Donor;
use Give\Framework\Models\Model;

/**
 * Class Subscription
 *
 * @unreleased
 */
class Subscription extends Model
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $createdAt;
    /**
     * @var string
     */
    public $expiresAt;
    /**
     * @var int
     */
    public $donorId;
    /**
     * @var string
     */
    public $period;
    /**
     * @var string
     */
    public $frequency;
    /**
     * @var int
     */
    public $times;
    /**
     * @var string
     */
    public $transactionId;
    /**
     * @var int
     */
    public $amount;
    /**
     * @var int
     */
    public $feeAmount;
    /**
     * @var string
     */
    public $status;
    /**
     * @var string
     */
    public $gatewaySubscriptionId;
    /**
     * @var array
     */
    public $notes;

    /**
     * @unreleased
     *
     * @param  int  $amount
     * @param  string  $period  // TODO: Make VO
     * @param  string  $frequency  // TODO: Make VO
     * @param  int  $donorId
     */
    public function __construct($amount, $period, $frequency, $donorId)
    {
        $this->amount = $amount;
        $this->period = $period;
        $this->frequency = $frequency;
        $this->donorId = $donorId;
        $this->createdAt = $this->getCurrentDateTime();
    }

    /**
     * Find subscription by ID
     *
     * @unreleased
     *
     * @param  int  $id
     * @return Subscription|null
     */
    public static function find($id)
    {
        return give()->subscriptionRepository->getById($id);
    }

    /**
     * @return Donor|null
     */
    public function donor()
    {
        return give()->donorRepository->getById($this->donorId);
    }

    /**
     * @return Donation[]
     */
    public function donations()
    {
        return give()->donationRepository->getBySubscriptionId($this->id);
    }

    /**
     * Get Subscription notes
     *
     * @return object[]
     */
    public function getNotes()
    {
        return give()->subscriptionRepository->getNotesBySubscriptionId($this->id);
    }

}
