<?php

namespace Give\NextGen\DonationForm\DataTransferObjects;

/**
 * @since 0.1.0
 */
class DonateRouteData
{
    /**
     * @var string
     */
    public $routeSignature;
    /**
     * @var string
     */
    public $routeSignatureId;
    /**
     * @var string
     */
    public $routeSignatureExpiration;

    /**
     * Convert data from request into DTO
     *
     * @since 0.1.0
     */
    public static function fromRequest(array $request): DonateRouteData
    {
        $self = new static();

        $self->routeSignature = $request['givewp-route-signature'];
        $self->routeSignatureId = $request['givewp-route-signature-id'];
        $self->routeSignatureExpiration = $request['givewp-route-signature-expiration'];

        return $self;
    }
}
