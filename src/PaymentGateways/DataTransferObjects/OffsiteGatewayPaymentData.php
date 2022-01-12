<?php

namespace Give\PaymentGateways\DataTransferObjects;

use Give\Framework\PaymentGateways\Actions\GenerateGatewayRouteUrl;
use Give\Helpers\Call;

/**
 * @unreleased
 */
class OffsiteGatewayPaymentData extends GatewayPaymentData
{
    /**
     * @var mixed|string|void
     */
    public $failedRedirectUrl;

    /**
     * @inerhitDoc
     *
     * @unreleased
     *
     * @return OffsiteGatewayPaymentData
     */
    public static function fromArray(array $array)
    {
        /* @var OffsiteGatewayPaymentData $self */
        $self = parent::fromArray($array);

        // Gateway route for successful payment.
        $self->redirectUrl = Call::invoke(
            GenerateGatewayRouteUrl::class,
            $self->gatewayId,
            'returnSuccessFromOffsiteRedirect',
            $self->donationId
        );

        // Gateway route for failure payment.
        $self->failedRedirectUrl = Call::invoke(
            GenerateGatewayRouteUrl::class,
            $self->gatewayId,
            'returnFailureFromOffsiteRedirect',
            $self->donationId
        );

        return $self;
    }

}
