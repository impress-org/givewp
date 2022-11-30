<?php

namespace Give\Subscriptions\DataTransferObjects;

use DateTime;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Framework\Support\ValueObjects\Money;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionMode;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

/**
 * Class SubscriptionObjectData
 *
 * @since 2.19.6
 */
final class SubscriptionQueryData
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
    public $renewsAt;
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
     * @var SubscriptionMode
     */
    public $mode;
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
     */
    public static function fromObject($subscriptionQueryObject): self
    {
        $self = new static();

        $self->id = (int)$subscriptionQueryObject->id;
        $self->createdAt = Temporal::toDateTime($subscriptionQueryObject->createdAt);
        $self->renewsAt = isset($subscriptionQueryObject->renewsAt) ? Temporal::toDateTime(
            $subscriptionQueryObject->renewsAt
        ) : null;
        $self->donorId = (int)$subscriptionQueryObject->donorId;
        $self->period = new SubscriptionPeriod($subscriptionQueryObject->period);
        $self->frequency = (int)$subscriptionQueryObject->frequency;
        $self->installments = (int)$subscriptionQueryObject->installments;
        $self->transactionId = $subscriptionQueryObject->transactionId;
        $self->mode = new SubscriptionMode($subscriptionQueryObject->mode);
        $self->amount = Money::fromDecimal($subscriptionQueryObject->amount, $subscriptionQueryObject->currency ?? give_get_currency());
        $self->feeAmountRecovered = Money::fromDecimal($subscriptionQueryObject->feeAmount,
            $subscriptionQueryObject->currency ?? give_get_currency());
        $self->status = new SubscriptionStatus($subscriptionQueryObject->status);
        $self->gatewayId = $subscriptionQueryObject->gatewayId;
        $self->gatewaySubscriptionId = $subscriptionQueryObject->gatewaySubscriptionId;
        $self->donationFormId = (int)$subscriptionQueryObject->donationFormId;

        return $self;
    }

    /**
     * Convert DTO to Subscription
     */
    public function toSubscription(): Subscription
    {
        $attributes = get_object_vars($this);

        return new Subscription($attributes);
    }
}
