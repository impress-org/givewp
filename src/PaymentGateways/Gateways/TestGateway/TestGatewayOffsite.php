<?php

namespace Give\PaymentGateways\Gateways\TestGateway;

use Give\Framework\PaymentGateways\Commands\PaymentCommand;
use Give\Framework\PaymentGateways\Commands\PaymentProcessing;
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

    /**
     * An example of using the provided offsite method of returning from a redirect.
     *
     * @inheritDoc
     */
    public function returnFromOffsiteRedirect($donationId)
    {
        $this->updateDonation($donationId);

        return response()->redirectTo(give_get_success_page_uri());
    }

    /**
     * An example gateway method for extending the Gateway API for a given gateway.
     *
     * @param  int  $donationId
     * @return JsonResponse
     */
    public function testGatewayMethod($donationId)
    {
        $this->updateDonation($donationId);

        return response()->json();
    }

    /**
     * Helper for updating donation
     *
     * @param $donationId
     * @return void
     */
    private function updateDonation($donationId)
    {
        give_insert_payment_note($donationId, 'NOTE GOES HERE');
        give_update_payment_status($donationId);
        give_set_payment_transaction_id($donationId, "test-gateway-transaction-id");
    }

    public function returnCancelFromOffsiteRedirect($donationId)
    {
        // TODO: Implement returnCancelFromOffsiteRedirect() method.
    }
}
