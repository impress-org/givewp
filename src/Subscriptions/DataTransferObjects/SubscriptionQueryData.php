<?php

namespace Give\Subscriptions\DataTransferObjects;

use Give\Subscriptions\Models\Subscription;

/**
 * Class SubscriptionObjectData
 *
 * @unreleased
 */
class SubscriptionQueryData
{
    /**
     * @var int
     */
    private $id;
    /**
     * @var string
     */
    private $createdAt;
    /**
     * @var string
     */
    private $updatedAt;
    /**
     * @var string
     */
    private $status;
    /**
     * @var string
     */
    private $expiresAt;
    /**
     * @var int
     */
    private $donorId;
    /**
     * @var string
     */
    private $period;
    /**
     * @var string
     */
    private $frequency;
    /**
     * @var int
     */
    private $times;
    /**
     * @var string
     */
    private $transactionId;
    /**
     * @var int
     */
    private $amount;
    /**
     * @var int
     */
    private $feeAmount;
    /**
     * @var string
     */
    private $gatewaySubscriptionId;
    /**
     * @var string[]
     */
    private $notes;

    /**
     * Convert data from Subscription Object to Subscription Model
     *
     * @unreleased
     *
     * @return self
     */
    public static function fromObject($object)
    {
        $self = new static();

        $self->id = (int)$object->id;
        $self->createdAt = $object->created;
        $self->expiresAt = $object->expiration;
        $self->donorId = (int)$object->customer_id;
        $self->period = $object->period;
        $self->frequency = (int)$object->frequency;
        $self->times = (int)$object->bill_times;
        $self->transactionId = $object->transaction_id;
        $self->amount = (int)$object->recurring_amount;
        $self->feeAmount = (int)$object->recurring_fee_amount;
        $self->status = $object->status;
        $self->gatewaySubscriptionId = $object->profile_id;
        $self->notes = $object->notes;

        return $self;
    }

    /**
     * Convert DTO to Subscription
     *
     * @return Subscription
     */
    public function toSubscription()
    {
        $subscription = new Subscription();

        $subscription->id = $this->id;
        $subscription->createdAt = $this->createdAt;
        $subscription->expiresAt = $this->expiresAt;
        $subscription->donorId = $this->donorId;
        $subscription->period = $this->period;
        $subscription->frequency = $this->frequency;
        $subscription->times = $this->times;
        $subscription->transactionId = $this->transactionId;
        $subscription->amount = $this->amount;
        $subscription->feeAmount = $this->feeAmount;
        $subscription->status = $this->status;
        $subscription->gatewaySubscriptionId = $this->gatewaySubscriptionId;
        $subscription->notes = $this->notes;

        return $subscription;
    }
}
