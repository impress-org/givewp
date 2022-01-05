<?php

namespace Give\PaymentGateways\PayPalStandard;

use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Helpers\Call;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\PayPalStandard\Actions\CreatePayPalStandardPaymentURL;
use Give\PaymentGateways\PayPalStandard\Views\PayPalStandardBillingFields;

/**
 * This class handles one-time donation payment processing with PayPal Standard payment gateway
 *
 * @unlreased
 */
class PayPalStandardGateway extends PaymentGateway
{
    /**
     * @inheritDoc
     */
    public function getLegacyFormFieldMarkup($formId)
    {
        Call::invoke(PayPalStandardBillingFields::class, $formId);
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
        // Before registering PayPal Standard with new payment gateway API, We were using this url
        // as offsite redirect url. Query param `payment-confirmation` is important because it triggers filter hook.
        // We are using this url for backward compatibility.
        // To review usage, search for `give_payment_confirm_paypal` filter hook.
        $redirectUrl = add_query_arg(
            ['payment-confirmation' => 'paypal', 'payment-id' => $paymentData->donationId,],
            give_get_success_page_uri()
        );

        return new RedirectOffsite(Call::invoke(CreatePayPalStandardPaymentURL::class, $paymentData, $redirectUrl));
    }
}
