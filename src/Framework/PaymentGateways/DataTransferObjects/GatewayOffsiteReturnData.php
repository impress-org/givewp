<?php

namespace Give\Framework\PaymentGateways\DataTransferObjects;

/**
 * Class GatewayOffsiteReturnData
 * @unreleased
 */
class GatewayOffsiteReturnData
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

        return $self;
    }
}
