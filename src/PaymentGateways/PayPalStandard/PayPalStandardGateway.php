<?php

namespace Give\PaymentGateways\PayPalStandard;

use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\Types\OffSitePaymentGateway;
use Give\Helpers\Call;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\PayPalStandard\Actions\BuildPayPalStandardPaymentURL;

use function Give\Framework\Http\Response\response;

/**
 * This class handles one-time donation payment processing with PayPal Standard payment gateway
 *
 * @unlreased
 */
class PayPalStandardGateway extends OffSitePaymentGateway
{
    /**
     * @inheritDoc
     */
    public function getLegacyFormFieldMarkup($formId)
    {
        // TODO: use give_paypal_standard_billing_fields function
        // TODO: Implement getLegacyFormFieldMarkup() method.
    }

    /**
     * @inheritDoc
     */
    public static function id()
    {
        return 'paypal';
    }

    /**
     * @inerhitDoc
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
        return esc_html__('PayPal Standard', 'give');
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel()
    {
        return esc_html__('PayPal', 'give');
    }

    /**
     * @inheritDoc
     */
    public function createPayment(GatewayPaymentData $paymentData)
    {
        return new RedirectOffsite( Call::invoke( BuildPayPalStandardPaymentURL::class, $paymentData ) );
    }

    /**
     * @inerhitDoc
     */
    public function returnFromOffsiteRedirect()
    {
        // Leave it empty.
    }

    /**
     * @inerhitDoc
     *
     * @param $donationId
     * @param $method
     *
     * @return void
     */
    public function handleGatewayRouteMethod($donationId, $method)
    {
        // Redirect to receipt.
        $response = response()->redirectTo(give_get_success_page_uri());
        $this->handleResponse($response);
    }
}
