<?php

namespace Give\Subscriptions\Models;

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
    public static function find($id)
    {
        return give()->subscriptionRepository->getById($id);
    }

}
