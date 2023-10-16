<?php

namespace Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\DataTransferObjects;

class StripeGatewayData
{
    /**
     * @var string
     */
    public $stripeConnectedAccountId;
    /**
     * @var string
     */
    public $successUrl;
    /**
     * @var string
     */
    public $stripePaymentMethod;
    /**
     * @var bool
     */
    public $stripePaymentMethodIsCreditCard;

    /**
     * @since 3.0.0
     *
     * @param  array{stripeConnectedAccountKey: string, stripePaymentIntentId: string}  $request
     * @return StripeGatewayData
     */
    public static function fromRequest(array $request): StripeGatewayData
    {
        $self = new self();
        $self->stripePaymentMethod = $request['stripePaymentMethod'];
        $self->stripePaymentMethodIsCreditCard = $request['stripePaymentMethodIsCreditCard'];
        $self->stripeConnectedAccountId = $request['stripeConnectedAccountId'];
        $self->successUrl = rawurldecode($request['successUrl']);

        return $self;
    }
}