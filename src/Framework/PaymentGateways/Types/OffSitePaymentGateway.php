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
     * Return redirect command (payment url) for offsite payment.
     *
     * @unreleased
     *
     * @param GatewayPaymentData|OffsiteGatewayPaymentData $paymentData Payment data
     *
     * @return RedirectOffsite
     */
    public function createPayment(GatewayPaymentData $paymentData)
    {
        return new RedirectOffsite(
            Call::invoke($this->getOffsitePaymentUrlCommand(), $paymentData)
        );
    }
}
