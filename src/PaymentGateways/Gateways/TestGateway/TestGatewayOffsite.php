<?php

namespace Give\PaymentGateways\Gateways\TestGateway;

use Give\Framework\PaymentGateways\CommandHandlers\PaymentCompleteHandler;
use Give\Framework\PaymentGateways\Commands\PaymentCancelled;
use Give\Framework\PaymentGateways\Commands\PaymentCommand;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\Http\Response\Types\JsonResponse;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\Types\OffSitePaymentGateway;
use Give\Helpers\Call;
use Give\Helpers\Form\Utils as FormUtils;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\Gateways\TestGateway\Commands\CreateTestGatewayOffsitePaymentUrlCommand;
use Give\PaymentGateways\Gateways\TestGateway\Views\LegacyFormFieldMarkup;

use function Give\Framework\Http\Response\response;

/**
 * Class TestGatewayOffsite
 * @since 2.18.0
 */
class TestGatewayOffsite extends OffSitePaymentGateway
{
    /**
     * @unreleased
     * @var string[]
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
     * @unreleased
     *
     * @param GatewayPaymentData $paymentData
     *
     * @return RedirectOffsite
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
        $command = new PaymentComplete( 'test-gateway-transaction-id' );
        $command->paymentNotes = ['NOTE GOES HERE'];

        return $command;
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
        return new PaymentCancelled($donationId);
    }

    /**
     * An example gateway method for extending the Gateway API for a given gateway.
     *
     * @param int $donationId
     *
     * @return JsonResponse
     */
    public function testGatewayMethod($donationId)
    {
        $command = new PaymentComplete();
        $command->gatewayTransactionId = 'test-gateway-transaction-id';
        $command->paymentNotes = ['NOTE GOES HERE'];
        PaymentCompleteHandler::make($command)->handle($donationId);

        return response()->json();
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
        return new PaymentCancelled($donationId);
    }
}
