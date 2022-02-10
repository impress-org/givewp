<?php

namespace Give\Subscriptions\Models;

use Give\Donations\Models\Donation;
use Give\Donors\Models\Donor;
use Give\Framework\Models\Model;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

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
     * @var SubscriptionPeriod
     */
    public $period;
    /**
     * @var int
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
     * @var SubscriptionStatus
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
     * @param  SubscriptionPeriod  $period
     * @param  string  $frequency
     * @param  int  $donorId
     */
    public function __construct($amount, $period, $frequency, $donorId)
    {
        $this->amount = $amount;
        $this->period = $period;
        $this->frequency = $frequency;
        $this->donorId = $donorId;
        $this->createdAt = $this->getCurrentDateTime();
        $this->status = SubscriptionStatus::PENDING();
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
