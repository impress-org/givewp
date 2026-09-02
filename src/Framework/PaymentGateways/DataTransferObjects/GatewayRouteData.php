<?php

namespace Give\Framework\PaymentGateways\DataTransferObjects;

/**
 * Class GatewayRouteData
 * @since      2.18.0
 * @since 2.23.1 Make class final to avoid unsafe usage of `new static()`.
 */
final class GatewayRouteData
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
     * @var array
     */
    public $queryParams;
    /**
     * @var string|null
     */
    public $routeSignature;
    /**
     * @var string|null
     */
    public $routeSignatureId;
    /**
     * @var string|null
     */
    public $routeSignatureExpiration;
    /**
     * Keys of the query args the signature covers.
     *
     * @since 4.16.8
     * @var string[]
     */
    public $routeSignatureArgKeys = [];

    /**
     * Query args the route framework itself sets. A gateway arg with one of these names would be
     * overwritten on the URL and excluded from queryParams on the way back, so a signature covering
     * it could never validate.
     *
     * @since 4.16.8
     */
    const ROUTE_PARAMS = [
        'give-listener',
        'give-gateway-id',
        'give-gateway-method',
        'give-route-signature',
        'give-route-signature-id',
        'give-route-signature-expiration',
        'give-route-signature-args',
    ];

    /**
     * Convert data from request into DTO
     *
     * @since 4.16.8 add routeSignatureArgKeys
     * @since 2.19.5 add routeSignatureExpiration
     * @since 2.19.4 add give-route-signature-id
     * @since 2.18.0
     *
     * @return self
     */
    public static function fromRequest(array $request)
    {
        $self = new static();

        $self->gatewayId = $request['give-gateway-id'];
        $self->gatewayMethod = $request['give-gateway-method'];
        $self->routeSignature = isset($request['give-route-signature']) ? $request['give-route-signature'] : null;
        $self->routeSignatureId = isset($request['give-route-signature-id']) ? $request['give-route-signature-id'] : null;
        $self->routeSignatureExpiration = isset($request['give-route-signature-expiration']) ? $request['give-route-signature-expiration'] : null;
        $self->routeSignatureArgKeys = empty($request['give-route-signature-args'])
            ? []
            : explode(',', $request['give-route-signature-args']);

        $self->queryParams = array_diff_key($request, array_flip(self::ROUTE_PARAMS));

        return $self;
    }
}
