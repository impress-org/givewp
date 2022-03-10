<?php

namespace Give\PaymentGateways\Gateways\TestGateway;

use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
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
        'returnFromOffsiteRedirect'
    ];

    /**
     * @inheritDoc
     */
    public $secureRouteMethods = [
        'securelyReturnFromOffsiteRedirect'
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
        $redirectUrl = $this->generateSecureGatewayRouteUrl(
            'securelyReturnFromOffsiteRedirect',
            $paymentData->donationId,
            ['give-donation-id' => $paymentData->donationId]
        );

        return new RedirectOffsite($redirectUrl);
    }

    /**
     * An example of using a routeMethod for extending the Gateway API to handle a redirect.
     *
     * @since 2.19.0
     *
     * @param  array  $queryParams
     * @throws PaymentGatewayException
     */
    public function returnFromOffsiteRedirect($queryParams)
    {
        $donationId = $queryParams['give-donation-id'];

        if (!get_post($donationId)) {
            throw new PaymentGatewayException('Donation does not exist');
        }

        $this->updateDonation($donationId);

        return response()->redirectTo(give_get_success_page_uri());
    }

    /**
     * An example of using a secureRouteMethod for extending the Gateway API to handle a redirect.
     *
     * @since 2.19.0
     *
     * @param  array  $queryParams
     * @throws PaymentGatewayException
     */
    public function securelyReturnFromOffsiteRedirect($queryParams)
    {
        $donationId = $queryParams['give-donation-id'];

        if (!get_post($donationId)) {
            throw new PaymentGatewayException('Donation does not exist');
        }

        $this->updateDonation($donationId);

        return response()->redirectTo(give_get_success_page_uri());
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
