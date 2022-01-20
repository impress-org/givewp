<?php

namespace Give\PaymentGateways\DataTransferObjects;

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
     * Convert data from array into DTO
     *
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

        return $self;
    }
}
