<?php

namespace Give\PaymentGateways\Gateways\Stripe\Actions;

use Give\LegacyPaymentGateways\DataTransferObjects\LegacyDonationData;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\Gateways\Stripe\ValueObjects\DonationSummary;
use Give\PaymentGateways\Gateways\Stripe\ValueObjects\PaymentIntent;
use Give\PaymentGateways\Gateways\Stripe\ValueObjects\PaymentMethod;
use Give\PaymentGateways\Gateways\Stripe\WorkflowAction;
use Give\ValueObjects\Money;

/**
 * @unreleased
 */
class CreatePaymentIntent extends WorkflowAction
{
    /** @var array */
    protected $paymentIntentArgs;

    /**
     * @unreleased
     * @param array $paymentIntentArgs
     */
    public function __construct( $paymentIntentArgs = [] )
    {
        $this->paymentIntentArgs = $paymentIntentArgs;
    }

    /**
     * @unreleased
     * @param GatewayPaymentData $paymentData
     * @param DonationSummary $donationSummary
     * @param \Give_Stripe_Customer $giveStripeCustomer
     * @param PaymentMethod $paymentMethod
     * @return void
     */
    public function __invoke(
        GatewayPaymentData $paymentData,
        DonationSummary $donationSummary,
        \Give_Stripe_Customer $giveStripeCustomer,
        PaymentMethod $paymentMethod
    )
    {
        /**
         * This filter hook is used to update the payment intent arguments.
         *
         * @since 2.5.0
         */
        $intent_args = apply_filters(
            'give_stripe_create_intent_args',
            array_merge( [
                'amount' => Money::of($paymentData->price, $paymentData->currency)->getMinorAmount(),
                'currency' => $paymentData->currency,
                'payment_method_types' => [ 'card' ],
                'statement_descriptor' => give_stripe_get_statement_descriptor(),
                'description' => $donationSummary->getSummary(),
                'metadata' => give_stripe_prepare_metadata($paymentData->donationId, new LegacyDonationData( $paymentData, $paymentMethod->id() ) ),
                'customer' => $giveStripeCustomer->get_id(),
                'payment_method' => $paymentMethod->id(),
                'confirm' => true,
                'return_url' => $paymentData->redirectUrl,
            ], $this->paymentIntentArgs )
        );

        // Send Stripe Receipt emails when enabled.
        if ( give_is_setting_enabled( give_get_option( 'stripe_receipt_emails' ) ) ) {
            $intent_args['receipt_email'] = $paymentData->donorInfo->email;
        }

        $intent = give( PaymentIntent::class )->create( $intent_args );

        give_insert_payment_note( $paymentData->donationId, sprintf( __( 'Stripe Charge/Payment Intent ID: %s', 'give' ), $intent->id() ) );
        give_insert_payment_note( $paymentData->donationId, sprintf( __( 'Stripe Payment Intent Client Secret: %s', 'give' ), $intent->clientSecret() ) );
        give_update_meta( $paymentData->donationId, '_give_stripe_payment_intent_client_secret', $intent->clientSecret() );

        if( 'requires_action' == $intent->status() ) {
            give_insert_payment_note($paymentData->donationId, __( 'Stripe requires additional action to be fulfilled. Check your Stripe account.', 'give' ));
            give_update_meta($paymentData->donationId, '_give_stripe_payment_intent_require_action_url', $intent->nextActionRedirectUrl());
        }

        $this->bind( $intent );
    }
}
