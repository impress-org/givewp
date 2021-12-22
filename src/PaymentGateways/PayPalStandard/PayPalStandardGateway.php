<?php

namespace Give\PaymentGateways\PayPalStandard;

use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\Types\OffSitePaymentGateway;
use Give\Helpers\Call;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\PayPalStandard\Actions\BuildPayPalStandardPaymentURL;
use Give\PaymentGateways\PayPalStandard\Views\PayPalStandardBillingFields;

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
        Call::invoke(PayPalStandardBillingFields::class);
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
        $redirectUrl = $this->generateReturnUrlFromRedirectOffsite($paymentData->donationId);

        return new RedirectOffsite(Call::invoke(BuildPayPalStandardPaymentURL::class, $paymentData, $redirectUrl));
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
        // Before registering PayPal Standard with new payment gateway API, We were using this url
        // as offsite redirect url. Query param `payment-confirmation` is important because it triggers filter hook.
        // We are using this url for backward compatibility.
        // To review usage, search for `give_payment_confirm_paypal` filter hook.
        $receiptPageUrl = add_query_arg(
            ['payment-confirmation' => 'paypal', 'payment-id' => $donationId,],
            give_get_success_page_uri()
        );

        // Redirect to receipt.
        $response = response()->redirectTo($receiptPageUrl);
        $this->handleResponse($response);
    }
}
