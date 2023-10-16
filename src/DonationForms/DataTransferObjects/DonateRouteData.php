<?php

namespace Give\DonationForms\DataTransferObjects;

use Give\DonationForms\Routes\DonateRouteSignature;
use Give\Framework\PaymentGateways\Log\PaymentGatewayLog;

/**
 * @since 3.0.0
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
     * @since 3.0.0
     */
    public static function fromRequest(array $request): self
    {
        $self = new self();

        $self->routeSignature = $request['givewp-route-signature'];
        $self->routeSignatureId = $request['givewp-route-signature-id'];
        $self->routeSignatureExpiration = $request['givewp-route-signature-expiration'];

        return $self;
    }

    /**
     * @since 3.0.0
     *
     * @return void
     */
    public function validateSignature()
    {
        $signature = new DonateRouteSignature(
            $this->routeSignatureId,
            $this->routeSignatureExpiration
        );

        if (!$signature->isValid($this->routeSignature)) {
            PaymentGatewayLog::error(
                'Invalid Secure Route',
                [
                    'routeSignature' => $this->routeSignature,
                    'signature' => $signature,
                    'signatureString' => $signature->toString(),
                    'signatureHash' => $signature->toHash(),
                    'signatureExpiration' => $signature->expiration,
                ]
            );

            wp_die('Forbidden', 403);
        }
    }
}
