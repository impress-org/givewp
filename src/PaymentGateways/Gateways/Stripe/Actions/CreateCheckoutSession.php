<?php

namespace Give\PaymentGateways\Gateways\Stripe\Actions;

use Give\Framework\PaymentGateways\DonationSummary;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\Exceptions\InvalidPropertyName;
use Give\PaymentGateways\Gateways\Stripe\ValueObjects\CheckoutSession;
use Give\ValueObjects\Money;
use Give_Stripe_Customer;

/**
 * @since 2.19.0
 */
class CreateCheckoutSession
{
    /**
     * @since 2.19.0
     *
     * @param GatewayPaymentData $paymentData
     * @param DonationSummary $donationSummary
     * @param Give_Stripe_Customer $giveStripeCustomer
     *
     * @return CheckoutSession
     * @throws InvalidPropertyName
     */
    public function __invoke(
        GatewayPaymentData $paymentData,
        DonationSummary $donationSummary,
        Give_Stripe_Customer $giveStripeCustomer
    ) {
        $session_args = [
            'customer' => $giveStripeCustomer->get_id(),
            'client_reference_id' => $paymentData->purchaseKey,
            'payment_method_types' => ['card'],
            'billing_address_collection' => give_is_setting_enabled(
                give_get_option('stripe_collect_billing')
            ) ? 'required' : 'auto',
            'mode' => 'payment',
            'line_items' => [
                [
                    'name' => give_get_donation_form_title($paymentData->donationId),
                    'description' => $donationSummary->getSummaryWithDonor(),
                    'amount' => Money::of($paymentData->price, $paymentData->currency)->getMinorAmount(),
                    'currency' => $paymentData->currency,
                    'quantity' => 1,
                ],
            ],
            'payment_intent_data' => [
                'capture_method' => 'automatic',
                'description' => $donationSummary->getSummaryWithDonor(),
                'metadata' => give_stripe_prepare_metadata($paymentData->donationId),
                'statement_descriptor' => give_stripe_get_statement_descriptor(),
            ],
            'submit_type' => 'donate',
            'success_url' => give_get_success_page_uri(),
            'cancel_url' => give_get_failed_transaction_uri(),
            'locale' => give_stripe_get_preferred_locale(),
        ];

        // If featured image exists, then add it to checkout session.
        $formId = give_get_payment_form_id($paymentData->donationId);
        if (!empty(get_the_post_thumbnail($formId))) {
            $session_args['line_items'][0]['images'] = [get_the_post_thumbnail_url($formId)];
        }

        $session = give(CheckoutSession::class)->create($session_args);

        give_insert_payment_note($paymentData->donationId, 'Stripe Checkout Session ID: ' . $session->id());
        give_set_payment_transaction_id($paymentData->donationId, $session->id());

        return $session;
    }
}
