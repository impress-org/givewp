<?php

namespace Give\PaymentGateways\Gateways\Stripe;

use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\Commands\PaymentProcessing;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Helpers\Call;
use Give\Helpers\Form\Utils as FormUtils;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\Gateways\Stripe\Actions\CreatePayment;
use Give\PaymentGateways\Gateways\Stripe\Exceptions\PaymentIntentException;
use Give\PaymentGateways\Gateways\Stripe\Exceptions\PaymentMethodException;
use Give\PaymentGateways\Gateways\Stripe\Exceptions\StripeCustomerException;
use Give\ValueObjects\Money;
use Give_Stripe_Customer;
use Give_Stripe_Payment_Intent;

/**
 * @unreleased
 */
class CreditCardGateway extends PaymentGateway
{
    /**
     * @inheritDoc
     * @unreleased
     * @return GatewayCommand
     * @throws PaymentGatewayException
     */
    public function createPayment(GatewayPaymentData $paymentData)
    {
        if( empty( $_POST['give_stripe_payment_method'] ) ) throw new PaymentMethodException('Payment Method Not Found');

        $paymentMethodId = $_POST['give_stripe_payment_method'];
        $donationData = new LegacyDonationData( $paymentData, $paymentMethodId );

        $give_stripe_customer = new Give_Stripe_Customer( $paymentData->donorInfo->email, $paymentMethodId );
        if( $give_stripe_customer->get_id() ) {
            Helpers::save_stripe_customer_id( $give_stripe_customer->get_id(), $paymentData->donationId );
        } else {
            throw new StripeCustomerException( __( 'Unable to find or create stripe customer object.', 'give' ) );
        }

        give_insert_payment_note( $paymentData->donationId, 'Stripe Source/Payment Method ID: ' . $paymentMethodId );
        give_insert_payment_note( $paymentData->donationId, 'Stripe Customer ID: ' . $give_stripe_customer->get_id() );

        give_update_meta( $paymentData->donationId, '_give_stripe_source_id', $paymentMethodId );
        give_update_meta( $paymentData->donationId, '_give_stripe_customer_id', $give_stripe_customer->get_id() );
        give_update_meta( $paymentData->donationId, '_give_stripe_donation_summary', give_payment_gateway_donation_summary( $donationData->toArray(), false ) );

        /**
         * This filter hook is used to update the payment intent arguments.
         *
         * @since 2.5.0
         */
        $intent_args = apply_filters(
            'give_stripe_create_intent_args',
            [
                'amount'               => Money::of( $paymentData->price, $paymentData->currency )->getMinorAmount(),
                'currency'             => $paymentData->currency,
                'payment_method_types' => [ 'card' ],
                'statement_descriptor' => give_stripe_get_statement_descriptor(),
                'description'          => '', //give_payment_gateway_donation_summary( $donationData->toArray() ),
                'metadata'             => give_stripe_prepare_metadata( $paymentData->donationId, $donationData->toArray() ),
                'customer'             => $give_stripe_customer->get_id(),
                'payment_method'       => $paymentMethodId,
                'confirm'              => true,
                'return_url'           => $paymentData->redirectUrl,
            ]
        );

        // Send Stripe Receipt emails when enabled.
        if ( give_is_setting_enabled( give_get_option( 'stripe_receipt_emails' ) ) ) {
            $intent_args['receipt_email'] = $paymentData->donorInfo->email;
        }

        $intent = give( LegacyStripePaymentIntent::class )->create( $intent_args );

        give_insert_payment_note( $paymentData->donationId, 'Stripe Charge/Payment Intent ID: ' . $intent->id );
        give_insert_payment_note( $paymentData->donationId, 'Stripe Payment Intent Client Secret: ' . $intent->client_secret );
        give_update_meta( $paymentData->donationId, '_give_stripe_payment_intent_client_secret', $intent->client_secret );

        switch( $intent->status() )  {
            case 'requires_action':
                give_insert_payment_note( $paymentData->donationId, 'Stripe requires additional action to be fulfilled.' );
                give_update_meta( $paymentData->donationId, '_give_stripe_payment_intent_require_action_url', $intent->nextActionRedirectUrl() );
                return new RedirectOffsite( $intent->nextActionRedirectUrl() );
            case 'succeeded':
                return new PaymentProcessing( $intent->id() );
            default:
                throw new PaymentIntentException( sprintf( __( 'Unhandled payment intent status: %s', 'give' ), $intent->status() ) );
        }
    }

    /**
     * @inheritDoc
     */
    public static function id()
    {
        return 'stripe';
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

        // @TODO Migrate field markup from legacy gateway implementation.
    }
}
