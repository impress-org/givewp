<?php

namespace Give\PaymentGateways\DataTransferObjects;

/**
 * Class SubscriptionData
 * @since 2.18.0
 */
class SubscriptionData
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
     *
     * @return self
     */
    public static function fromRequest(array $request)
    {
        $self = new static();

        $self->period = $request['period'];
        $self->times = $request['times'];
        $self->frequency = $request['frequency'];

        return $self;
    }

    /**
     * @param  int  $subscriptionId
     * @return GatewaySubscriptionData
     */
    public function toGatewaySubscriptionData($subscriptionId)
    {
        return GatewaySubscriptionData::fromArray([
            'period' => $this->period,
            'times' => $this->times,
            'frequency' => $this->frequency,
            'subscriptionId' => $subscriptionId,
        ]);
    }
}
