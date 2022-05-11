<?php

namespace Give\Subscriptions\DataTransferObjects;

use DateTime;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Framework\Support\ValueObjects\Money;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

/**
 * Class SubscriptionObjectData
 *
 * @since 2.19.6
 */
class SubscriptionQueryData
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var DateTime
     */
    public $createdAt;
    /**
     * @var DateTime
     */
    public $expiresAt;
    /**
     * @var string
     */
    public $status;
    /**
     * @var int
     */
    public $donorId;
    /**
     * @var SubscriptionPeriod
     */
    public $period;
    /**
     * @var string
     */
    public $frequency;
    /**
     * @var int
     */
    public $installments;
    /**
     * @var string
     */
    public $transactionId;
    /**
     * @var Money
     */
    public $amount;
    /**
     * @var Money
     */
    public $feeAmountRecovered;
    /**
     * @var string
     */
    public $gatewayId;
    /**
     * @var string
     */
    public $gatewaySubscriptionId;
    /**
     * @var int
     */
    public $donationFormId;

    /**
     * Convert data from Subscription Object to Subscription Model
     *
     * @since 2.19.6
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
        $self->amount = Money::fromDecimal($subscriptionQueryObject->amount, $subscriptionQueryObject->currency);
        $self->feeAmountRecovered = Money::fromDecimal($subscriptionQueryObject->feeAmount, $subscriptionQueryObject->currency);
        $self->status = new SubscriptionStatus($subscriptionQueryObject->status);
        $self->gatewayId = $subscriptionQueryObject->gatewayId;
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
