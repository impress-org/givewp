<?php

namespace Give\PaymentGateways\Gateways\TestGateway;

use Give\Framework\PaymentGateways\Commands\PaymentCommand;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\Commands\PaymentProcessing;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\Types\OffSitePaymentGateway;
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
    public $routeMethods = [
        'testGatewayMethod'
    ];

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
     * @inheritDoc
     */
    public function createPayment(GatewayPaymentData $paymentData)
    {
        $redirectUrl = $this->generateReturnUrlFromRedirectOffsite($paymentData->donationId);

        return new RedirectOffsite($redirectUrl);
    }

    /**
     * @inheritDoc
     */
    public function returnFromOffsiteRedirect()
    {
        $transactionId = "test-gateway-transaction-id";

        return new PaymentComplete($transactionId);
    }

    /**
     * @return PaymentComplete
     */
    public function testGatewayMethod()
    {
        $transactionId = "test-gateway-transaction-id";

        return new PaymentComplete($transactionId);
    }

    /**
     * @inerhitDoc
     *
     * @unreleased
     *
     * @return string
     */
    protected function getOffsitePaymentUrlCommand()
    {
        return CreateTestGatewayOffsitePaymentUrlCommand::class;
    }

    /**
     * @unreleased
     *
     * @param int $donationId
     *
     * @return PaymentCommand
     */
    protected function returnSuccessFromOffsiteRedirect($donationId)
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
    protected function returnFailureFromOffsiteRedirect($donationId)
    {
        // TODO: return failed payment command
    }
}
