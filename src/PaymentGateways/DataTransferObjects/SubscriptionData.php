<?php

namespace Give\PaymentGateways\DataTransferObjects;

/**
 * Class SubscriptionData
 * @since 2.18.0
 */
final class SubscriptionData
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
     * Convert data from request into DTO
     *
     * @since 2.18.0
     */
    public static function fromRequest(array $request): SubscriptionData
    {
        $self = new static();

        $self->period = $request['period'];
        $self->times = $request['times'];
        $self->frequency = $request['frequency'];

        return $self;
    }
}
