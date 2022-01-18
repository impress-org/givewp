<?php

namespace Give\Subscriptions\Models;

use Give\Subscriptions\Repositories\SubscriptionRepository;

/**
 * Class Donor
 *
 * @unreleased
 */
class Subscription
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
     * @var string
     */
    public $notes;

    /**
     * Find subscription by ID
     *
     * @unreleased
     *
     * @param  int  $id
     * @return Subscription
     */
    public function find($id)
    {
        return give(SubscriptionRepository::class)->getById($id);
    }

}
