<?php

namespace Give\Subscriptions\DataTransferObjects;

use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

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
     * @var SubscriptionPeriod
     */
    private $period;
    /**
     * @var string
     */
    private $frequency;
    /**
     * @var int
     */
    private $installments;
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
     * @var int
     */
    private $donationFormId;

    /**
     * Convert data from Subscription Object to Subscription Model
     *
     * @unreleased
     *
     * @return self
     */
    public static function fromObject($subscriptionQueryObject)
    {
        $self = new static();

        $self->id = (int)$subscriptionQueryObject->id;
        $self->createdAt = $subscriptionQueryObject->created;
        $self->expiresAt = $subscriptionQueryObject->expiration;
        $self->donorId = (int)$subscriptionQueryObject->customer_id;
        $self->period = new SubscriptionPeriod($subscriptionQueryObject->period);
        $self->frequency = (int)$subscriptionQueryObject->frequency;
        $self->installments = (int)$subscriptionQueryObject->bill_times;
        $self->transactionId = $subscriptionQueryObject->transaction_id;
        $self->amount = (int)$subscriptionQueryObject->recurring_amount;
        $self->feeAmount = (int)$subscriptionQueryObject->recurring_fee_amount;
        $self->status = new SubscriptionStatus($subscriptionQueryObject->status);
        $self->gatewaySubscriptionId = $subscriptionQueryObject->profile_id;
        $self->notes = $subscriptionQueryObject->notes;
        $self->donationFormId = (int)$subscriptionQueryObject->product_id;

        return $self;
    }

    /**
     * Convert DTO to Subscription
     *
     * @return Subscription
     */
    public function toSubscription()
    {
        $subscription = new Subscription($this->amount, $this->period, $this->frequency, $this->donorId);

        $subscription->id = $this->id;
        $subscription->createdAt = $this->createdAt;
        $subscription->expiresAt = $this->expiresAt;
        $subscription->installments = $this->installments;
        $subscription->transactionId = $this->transactionId;
        $subscription->feeAmount = $this->feeAmount;
        $subscription->status = $this->status;
        $subscription->gatewaySubscriptionId = $this->gatewaySubscriptionId;
        $subscription->notes = $this->notes;
        $subscription->donationFormId = $this->donationFormId;

        return $subscription;
    }
}
