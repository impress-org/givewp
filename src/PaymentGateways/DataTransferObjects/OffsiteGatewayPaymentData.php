<?php

namespace Give\PaymentGateways\DataTransferObjects;

use Give\Framework\PaymentGateways\Commands\RedirectOffsiteFailedPayment;
use Give\Framework\PaymentGateways\Commands\RedirectOffsiteSuccessPayment;

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

        $redirectSuccessfulPaymentRedirect = new RedirectOffsiteSuccessPayment($self->donationId);
        $redirectFailedPaymentRedirect = new RedirectOffsiteFailedPayment($self->donationId);
        $self->redirectUrl = $redirectSuccessfulPaymentRedirect->getUrl($array['currencyUrl']);
        $self->failedRedirectUrl = $redirectFailedPaymentRedirect->getUrl($array['currencyUrl']);

        return $self;
    }

}
