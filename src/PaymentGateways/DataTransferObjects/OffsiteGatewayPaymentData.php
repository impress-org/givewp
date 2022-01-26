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
     * Redirect url for failed payment.
     *
     * @unreleased
     * @var string
     */
    public $failedRedirectUrl;

    /**
     * Redirect url for cancelled payment.
     *
     * @unreleased
     * @var string
     */
    public $cancelledRedirectUrl;

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
        $self->redirectUrl = wp_nonce_url(
            Call::invoke(
                GenerateGatewayRouteUrl::class,
                $self->gatewayId,
                'returnSuccessFromOffsiteRedirect',
                $self->donationId
            ),
            "returnSuccessFromOffsiteRedirect-$self->donationId"
        );

        // Gateway route for failed payment.
        $self->failedRedirectUrl = wp_nonce_url(
            Call::invoke(
                GenerateGatewayRouteUrl::class,
                $self->gatewayId,
                'returnFailureFromOffsiteRedirect',
                $self->donationId
            ),
            "returnFailureFromOffsiteRedirect-$self->donationId"
        );

        // Gateway route for cancelled payment.
        $self->cancelledRedirectUrl = wp_nonce_url(
            Call::invoke(
                GenerateGatewayRouteUrl::class,
                $self->gatewayId,
                'returnCancelFromOffsiteRedirect',
                $self->donationId
            ),
            "returnFailureFromOffsiteRedirect-$self->donationId"
        );

        return $self;
    }

}
