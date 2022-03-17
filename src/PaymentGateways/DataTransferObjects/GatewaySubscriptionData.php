<?php

namespace Give\PaymentGateways\DataTransferObjects;

use Give\Subscriptions\Models\Subscription;

/**
 * Class GatewaySubscriptionData
 * @since 2.18.0
 */
class GatewaySubscriptionData
{
    /**
     * @var string
     */
    public $period;

    /**
     * @var string
     */
    public $times;

    /**
     * @var string
     */
    public $frequency;
    /**
     * @var int
     */
    public $subscriptionId;
    /**
     * @var Subscription
     */
    public $subscription;

    /**
     * Convert data from array into DTO
     *
     * @unreleased added subscription model
     * @since 2.18.0
     *
     * @return self
     */
    public static function fromArray(array $array)
    {
        $self = new static();

        $self->period = $array['period'];
        $self->times = $array['times'];
        $self->frequency = $array['frequency'];
        $self->subscriptionId = $array['subscriptionId'];
        $self->subscription = $array['subscription'];

        return $self;
    }
}
