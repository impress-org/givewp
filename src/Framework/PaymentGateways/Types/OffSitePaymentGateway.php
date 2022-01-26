<?php

namespace Give\Framework\PaymentGateways\Types;

use Give\Framework\PaymentGateways\Contracts\OffsiteGatewayInterface;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\PaymentGateways\Traits\OffsiteGateway;

/**
 * @since 2.18.0
 */
abstract class OffSitePaymentGateway extends PaymentGateway implements OffsiteGatewayInterface
{
    use OffsiteGateway;

    /**
     * This route methods used on gateway route.
     * Check OffsiteGatewayPaymentData class for more details.
     *
     * @unreleased
     *
     * @var string[]
     */
    public $routeMethods = [
        'returnSuccessFromOffsiteRedirect',
        'returnFailureFromOffsiteRedirect'
    ];

    /**
     * Handle successful payment return.
     *
     * @unreleased
     *
     * @param int $donationId
     */
    abstract public function returnSuccessFromOffsiteRedirect($donationId);

    /**
     * Handle failure payment return.
     *
     * @unreleased
     *
     * @param int $donationId Donation
     */
    abstract public function returnFailureFromOffsiteRedirect($donationId);
}
