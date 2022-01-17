<?php

namespace Give\Framework\PaymentGateways\Types;

use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\Contracts\OffsiteGatewayInterface;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\PaymentGateways\Traits\OffsiteGateway;
use Give\Helpers\Call;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\DataTransferObjects\OffsiteGatewayPaymentData;

/**
 * @unreleased
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
    abstract protected function returnSuccessFromOffsiteRedirect($donationId);

    /**
     * Handle failure payment return.
     *
     * @unreleased
     *
     * @param int $donationId Donation
     */
    abstract protected function returnFailureFromOffsiteRedirect($donationId);

    /**
     * Return redirect command (response with payment url) for offsite payment.
     *
     * @unreleased
     *
     * @param GatewayPaymentData|OffsiteGatewayPaymentData $paymentData Payment data
     *
     * @return RedirectOffsite
     */
    abstract public function createPayment(GatewayPaymentData $paymentData);
}
