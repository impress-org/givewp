<?php

namespace Give\Subscriptions\DataTransferObjects;

use DateTime;
use Give\Framework\Support\Facades\DateTime\Temporal;
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
     * @var DateTime
     */
    private $createdAt;
    /**
     * @var DateTime
     */
    private $expiresAt;
    /**
     * @var string
     */
    private $status;
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
        $self->createdAt = Temporal::toDateTime($subscriptionQueryObject->createdAt);
        $self->expiresAt = isset($subscriptionQueryObject->expiration) ? Temporal::toDateTime(
            $subscriptionQueryObject->expiration
        ) : null;
        $self->donorId = (int)$subscriptionQueryObject->donorId;
        $self->period = new SubscriptionPeriod($subscriptionQueryObject->period);
        $self->frequency = (int)$subscriptionQueryObject->frequency;
        $self->installments = (int)$subscriptionQueryObject->installments;
        $self->transactionId = $subscriptionQueryObject->transactionId;
        $self->amount = (int)$subscriptionQueryObject->amount;
        $self->feeAmount = (int)$subscriptionQueryObject->feeAmount;
        $self->status = new SubscriptionStatus($subscriptionQueryObject->status);
        $self->gatewaySubscriptionId = $subscriptionQueryObject->gatewaySubscriptionId;
        $self->donationFormId = (int)$subscriptionQueryObject->donationFormId;

        return $self;
    }

    /**
     * Convert DTO to Subscription
     *
     * @return Subscription
     */
    public function toSubscription()
    {
        $attributes = get_object_vars($this);

        return new Subscription($attributes);
    }
}
