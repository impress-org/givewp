<?php

namespace Give\PaymentGateways\Gateways\TestGateway;

use Give\Framework\PaymentGateways\Commands\PaymentCommand;
use Give\Framework\PaymentGateways\Commands\PaymentProcessing;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\Types\OffSitePaymentGateway;
use Give\Helpers\Call;
use Give\Helpers\Form\Utils as FormUtils;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\Gateways\TestGateway\Commands\CreateTestGatewayOffsitePaymentUrlCommand;
use Give\PaymentGateways\Gateways\TestGateway\Views\LegacyFormFieldMarkup;

/**
 * Class TestGatewayOffsite
 * @unreleased
 */
class TestGatewayOffsite extends OffSitePaymentGateway
{
    /**
     * @inheritDoc
     */
    public static function id()
    {
        return 'test-gateway-offsite';
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return self::id();
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return __('Test Gateway Offsite', 'give');
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel()
    {
        return __('Test Gateway Offsite', 'give');
    }

    /**
     * @inheritDoc
     */
    public function getLegacyFormFieldMarkup($formId)
    {
        if (FormUtils::isLegacyForm($formId)) {
            return false;
        }

        /** @var LegacyFormFieldMarkup $legacyFormFieldMarkup */
        $legacyFormFieldMarkup = give(LegacyFormFieldMarkup::class);

        return $legacyFormFieldMarkup();
    }

    /**
     * @unreleased
     *
     * @param GatewayPaymentData $paymentData
     *
     * @return CreateTestGatewayOffsitePaymentUrlCommand|mixed
     */
    public function createPayment(GatewayPaymentData $paymentData)
    {
        return new RedirectOffsite(
            Call::invoke(
                CreateTestGatewayOffsitePaymentUrlCommand::class,
                $paymentData
            )
        );
    }

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
     * @unreleased
     *
     * @param int $donationId
     *
     * @return void
     */
    public function returnFailureFromOffsiteRedirect($donationId)
    {
        // TODO: return failed payment command
    }
}
