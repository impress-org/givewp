<?php

namespace Give\Framework\PaymentGateways\DataTransferObjects;

/**
 * Class GatewayRouteData
 * @unreleased
 */
class GatewayRouteData
{
    /**
     * @var string
     */
    public $gatewayId;
    /**
     * @var string
     */
    public $gatewayMethod;
    /**
     * @var int
     */
    public $donationId;

    /**
     * Convert data from request into DTO
     *
     * @unreleased
     *
     * @return self
     */
    public static function fromRequest(array $request)
    {
        $self = new static();

        $self->gatewayId = $request['give-gateway-id'];
        $self->gatewayMethod = $request['give-gateway-method'];
        $self->donationId = (int)$request['give-payment-id'];

        return $self;
    }
}
