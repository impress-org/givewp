<?php

namespace Give\NextGen\DonationForm\DataTransferObjects;

/**
 * @unreleased
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
     * @unreleased
     *
     * @return self
     */
    public static function fromRequest(array $request)
    {
        $self = new static();

        $self->routeSignature = $request['give-route-signature'];
        $self->routeSignatureId = $request['give-route-signature-id'];
        $self->routeSignatureExpiration = $request['give-route-signature-expiration'];

        return $self;
    }
}
