<?php

namespace Give\Framework\PaymentGateways\Types;

use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\Contracts\OffsiteGatewayInterface;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\PaymentGateways\Traits\OffsiteGateway;
use Give\Helpers\Call;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;

/**
 * @unreleased
 */
abstract class OffSitePaymentGateway extends PaymentGateway implements OffsiteGatewayInterface
{
    use OffsiteGateway;

    /**
     * Return command class name which returns offsite payment url.
     *
     * @unreleased
     *
     * @return string
     */
    abstract protected function getOffsitePaymentUrlCommand();

    /**
     * Handle successful payment return.
     *
     * @unreleased
     */
    abstract protected function returnSuccessFromOffsiteRedirect();

    /**
     * Handle failure payment return.
     *
     * @unreleased
     */
    abstract protected function returnFailureFromOffsiteRedirect();

    /**
     * Return redirect command (payment url) for offsite payment.
     *
     * @unreleased
     *
     * @return RedirectOffsite
     */
    public function createPayment(GatewayPaymentData $paymentData)
    {
        return new RedirectOffsite(
            Call::invoke(
                $this->getOffsitePaymentUrlCommand(),
                $paymentData,
                $this->generateReturnUrlFromRedirectOffsite(
                    $paymentData->donationId
                )
            )
        );
    }
}
