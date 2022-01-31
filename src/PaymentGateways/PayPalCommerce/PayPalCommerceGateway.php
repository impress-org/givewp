<?php

namespace Give\PaymentGateways\PayPalCommerce;

use Give\Framework\PaymentGateways\PaymentGateway;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;

/**
 * This class handle PayPal commerce:
 * - registration to new gateway api
 * - payment processing
 *
 * @unreleased
 */
class PayPalCommerceGateway extends PaymentGateway
{

    public function getLegacyFormFieldMarkup($formId, $args)
    {
    }

    public static function id()
    {
        return 'paypal-commerce';
    }

    public function getId()
    {
        return self::id();
    }

    public function getName()
    {
        return esc_html__('PayPal Donations', 'give');
    }

    public function getPaymentMethodLabel()
    {
        return esc_html__('Credit Card', 'give');
    }

    public function createPayment(GatewayPaymentData $paymentData)
    {
    }
}
