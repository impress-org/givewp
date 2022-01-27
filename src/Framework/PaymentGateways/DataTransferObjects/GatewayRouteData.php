<?php

namespace Give\Framework\PaymentGateways\DataTransferObjects;

/**
 * Class GatewayRouteData
 * @since 2.18.0
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
     * WordPress's nonce.
     * @var int
     */
    public $nonce;

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

        $self->gatewayId = $request['give-gateway-id'];
        $self->gatewayMethod = $request['give-gateway-method'];
        $self->donationId = (int)$request['give-donation-id'];

        // Nonce is options query param.
        // Check GenerateGatewayRouteUrl class.
        $self->nonce = isset( $request['_wpnonce'] ) ? $request['_wpnonce'] : null;

        return $self;
    }
}
