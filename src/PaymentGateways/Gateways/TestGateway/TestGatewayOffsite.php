<?php

namespace Give\PaymentGateways\Gateways\TestGateway;

use Give\Framework\Http\Response\Types\JsonResponse;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Helpers\Form\Utils as FormUtils;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\Gateways\TestGateway\Views\LegacyFormFieldMarkup;

use function Give\Framework\Http\Response\response;

/**
 * Class TestGatewayOffsite
 * @since 2.18.0
 */
class TestGatewayOffsite extends PaymentGateway
{
    /**
     * @inheritDoc
     */
    public $routeMethods = [
        'testGatewayMethod',
        'returnFromOffsiteRedirect'
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
    public function getLegacyFormFieldMarkup($formId, $args)
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
        $redirectUrl = $this->generateGatewayRouteUrl('returnFromOffsiteRedirect', $paymentData->donationId);

        return new RedirectOffsite($redirectUrl);
    }

    /**
     * An example of using a routeMethod to handing returning from a redirect.
     *
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
}
