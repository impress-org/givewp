<?php

namespace Give\PaymentGateways\Gateways\TestGateway;

use Give\Framework\Http\Response\Types\JsonResponse;
use Give\Framework\Http\Response\Types\RedirectResponse;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\Types\OffSitePaymentGateway;
use Give\Helpers\Form\Utils as FormUtils;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\Gateways\TestGateway\Views\LegacyFormFieldMarkup;

use function Give\Framework\Http\Response\response;

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
     * An example gateway method for extending the Gateway API for a given gateway.
     *
     * @param int $donationId
     * @return RedirectResponse|JsonResponse
     */
    public function testGatewayMethod( $donationId )
    {
        give_insert_payment_note( $donationId, 'NOTE GOES HERE' );
        give_update_payment_status( $donationId, 'complete' );
        give_set_payment_transaction_id( $donationId, "test-gateway-transaction-id" );

        return response()->json();
    }
}
