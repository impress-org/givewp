<?php

namespace Give\PaymentGateways\Gateways\Stripe\Actions;

use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\DonationSummary;
use Give\PaymentGateways\Exceptions\InvalidPropertyName;
use Give\PaymentGateways\Gateways\Stripe\ValueObjects\CheckoutSession;
use Give_Stripe_Customer;

/**
 * @since 2.19.0
 */
class CreateCheckoutSession
{
    /**
     * @since 2.20.0 Update function to get input value to line_items[0][name]
     * @since 2.19.0
     *
     * @return CheckoutSession
     * @throws InvalidPropertyName
     * @throws Exception
     */
    public function __invoke(
        Donation $donation,
        DonationSummary $donationSummary,
        Give_Stripe_Customer $giveStripeCustomer
    ) {
        $session_args = [
            'customer' => $giveStripeCustomer->get_id(),
            'client_reference_id' => $donation->purchaseKey,
            'payment_method_types' => ['card'],
            'billing_address_collection' => give_is_setting_enabled(
                give_get_option('stripe_collect_billing')
            ) ? 'required' : 'auto',
            'mode' => 'payment',
            'line_items' => [
                [
                    'name' => Donation::find($donation->id)->formTitle,
                    'description' => $donationSummary->getSummaryWithDonor(),
                    'amount' => $donation->amount->formatToMinorAmount(),
                    'currency' => $donation->amount->getCurrency(),
                    'quantity' => 1,
                ],
            ],
            'payment_intent_data' => [
                'capture_method' => 'automatic',
                'description' => $donationSummary->getSummaryWithDonor(),
                'metadata' => give_stripe_prepare_metadata($donation->id),
                'statement_descriptor' => give_stripe_get_statement_descriptor(),
            ],
            'submit_type' => 'donate',
            'success_url' => give_get_success_page_uri(),
            'cancel_url' => give_get_failed_transaction_uri(),
            'locale' => give_stripe_get_preferred_locale(),
        ];

        if ($formThumbnail = get_the_post_thumbnail($donation->formId)) {
            $session_args['line_items'][0]['images'] = [$formThumbnail];
        }

        $session = give(CheckoutSession::class)->create($session_args);

        DonationNote::create([
            'donationId' => $donation->id,
            'content' => sprintf(
                /* translators: 1: Stripe checkout payment method session id */
                esc_html__( 'Stripe Checkout Session ID: %s', 'give'),
                $session->id()
            )
        ]);

        $donation->gatewayTransactionId = $session->id();
        $donation->save();

        return $session;
    }
}
