<?php

namespace Give\Framework\PaymentGateways\Types;

use Give\Framework\PaymentGateways\Commands\PaymentCancelled;
use Give\Framework\PaymentGateways\Commands\PaymentCommand;
use Give\Framework\PaymentGateways\Commands\PaymentFailed;
use Give\Framework\PaymentGateways\Commands\PaymentProcessing;
use Give\Framework\PaymentGateways\Contracts\OffsiteGatewayInterface;
use Give\Framework\PaymentGateways\PaymentGateway;

/**
 * @since 2.18.0
 */
abstract class OffSitePaymentGateway extends PaymentGateway implements OffsiteGatewayInterface
{
    /**
     * @unreleased
     *
     * @param int $donationId
     *
     * @return PaymentCommand
     */
    public function returnSuccessFromOffsiteRedirect($donationId)
    {
        return new PaymentProcessing();
    }

    /**
     * Handle failed donation redirect.
     *
     * @unreleased
     *
     * @param int $donationId
     *
     * @return PaymentCommand
     */
    public function returnFailureFromOffsiteRedirect($donationId)
    {
        return new PaymentFailed();
    }

    /**
     * Handle cancelled donation redirect.
     *
     * @param int $donationId
     *
     * @return PaymentCancelled
     */
    public function returnCancelFromOffsiteRedirect($donationId)
    {
        return new PaymentCancelled();
    }
}
