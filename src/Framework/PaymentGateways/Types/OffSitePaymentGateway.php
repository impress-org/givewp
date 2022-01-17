<?php

namespace Give\Framework\PaymentGateways\Types;

use Give\Framework\Http\Response\Types\RedirectResponse;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\Commands\RedirectOffsiteFailedPayment;
use Give\Framework\PaymentGateways\Commands\RedirectOffsiteSuccessPayment;
use Give\Framework\PaymentGateways\Contracts\OffsiteGatewayInterface;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\PaymentGateways\Traits\OffsiteGateway;
use Give\Helpers\Call;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\DataTransferObjects\OffsiteGatewayPaymentData;
use Give\Session\SessionDonation\DonationAccessor;

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
     *
     * @param int $donationId
     */
    protected function returnSuccessFromOffsiteRedirect($donationId)
    {
        $this->handleResponse(new RedirectResponse((new RedirectOffsiteFailedPayment($donationId))
            ->getUrl((new DonationAccessor())->get()->currentUrl)));
    }

    /**
     * Handle failure payment return.
     *
     * @unreleased
     *
     * @param int $donationId Donation
     */
    protected function returnFailureFromOffsiteRedirect($donationId)
    {
        $this->handleResponse(new RedirectResponse((new RedirectOffsiteSuccessPayment($donationId))
            ->getUrl((new DonationAccessor())->get()->currentUrl)));
    }

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
