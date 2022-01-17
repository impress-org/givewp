<?php

namespace Give\PaymentGateways\Gateways\Stripe;

use Give\Framework\PaymentGateways\Commands\PaymentProcessing;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Helpers\Form\Utils as FormUtils;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;

/**
 * @unreleased
 */
class CreditCardGateway extends PaymentGateway
{
    /**
     * @inheritDoc
     */
    public function createPayment(GatewayPaymentData $paymentData)
    {
        // Get Payment Method from Stripe API.
        if( empty( $donation_data['post_data']['give_stripe_payment_method'] ) ) {
            throw new \Exception( 'Payment Method Not Found');
        }
        $payment_method_id = $donation_data['post_data']['give_stripe_payment_method'];


        // Get or Create Stripe Customer based on donor email and payment method.
        // Create Payment Intent.
        // Process additional steps for SCA or 3D secure.

        // Return payment "processing". The donation will be completed via webhook.
        return new PaymentProcessing();
    }

    /**
     * @inheritDoc
     */
    public static function id()
    {
        return 'stripe-credit-card-gateway';
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
        return __('Stripe - Credit Card', 'give');
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel()
    {
        return __('Stripe - Credit Card', 'give');
    }

    /**
     * @inheritDoc
     */
    public function getLegacyFormFieldMarkup($formId)
    {
        if (FormUtils::isLegacyForm($formId)) {
            return false;
        }

        return 'STRIPE CREDIT CARD FIELDS HERE';
    }
}
