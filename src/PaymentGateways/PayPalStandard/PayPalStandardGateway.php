<?php

namespace Give\PaymentGateways\PayPalStandard;

use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\Types\OffSitePaymentGateway;
use Give\Helpers\Call;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\PayPalStandard\Actions\CreatePayPalStandardPaymentURL;
use Give\PaymentGateways\PayPalStandard\Views\PayPalStandardBillingFields;

/**
 * This class handles one-time donation payment processing with PayPal Standard payment gateway
 *
 * @unlreased
 */
class PayPalStandardGateway extends OffSitePaymentGateway
{
    public $routeMethods = [
        'handleIpnNotification'
    ];

    /**
     * @inheritDoc
     */
    public function getLegacyFormFieldMarkup($formId, $args)
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
        return new RedirectOffsite(Call::invoke(CreatePayPalStandardPaymentURL::class, $paymentData));
    }

    /**
     * Handle PayPal IPN notification.
     *
     * @unreleased
     */
    public function handleIpnNotification(){
        give_process_paypal_ipn();
    }
}
