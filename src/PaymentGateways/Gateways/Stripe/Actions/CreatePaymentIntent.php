<?php

namespace Give\PaymentGateways\Gateways\Stripe\Actions;

use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\DonationSummary;
use Give\PaymentGateways\Exceptions\InvalidPropertyName;
use Give\PaymentGateways\Gateways\Stripe\ValueObjects\PaymentIntent;
use Give\PaymentGateways\Gateways\Stripe\ValueObjects\PaymentMethod;

/**
 * @since 2.19.0
 */
class CreatePaymentIntent
{
    /** @var array */
    protected $defaultIntentArgs;

    /**
     * @since 2.19.0
     */
    public function __construct(array $paymentIntentArgs = [])
    {
        $this->defaultIntentArgs = $paymentIntentArgs;
    }

    /**
     * @since 2.19.0
     *
     * @throws InvalidPropertyName
     * @throws Exception
     */
    public function __invoke(
        Donation $donation,
        DonationSummary $donationSummary,
        \Give_Stripe_Customer $giveStripeCustomer,
        PaymentMethod $paymentMethod
    ): PaymentIntent {
        /**
         * This filter hook is used to update the payment intent arguments.
         *
         * @since 2.5.0
         */
        $intent_args = apply_filters(
            'give_stripe_create_intent_args',
            array_merge([
                'amount' => $donation->amount->formatToMinorAmount(),
                'currency' => $donation->amount->getCurrency(),
                'payment_method_types' => ['card'],
                'statement_descriptor' => give_stripe_get_statement_descriptor(),
                'description' => $donationSummary->getSummaryWithDonor(),
                'metadata' => give_stripe_prepare_metadata($donation->id),
                'customer' => $giveStripeCustomer->get_id(),
                'payment_method' => $paymentMethod->id(),
                'confirm' => true,
                'return_url' => give_get_success_page_uri(),
            ], $this->defaultIntentArgs)
        );

        // Send Stripe Receipt emails when enabled.
        if (give_is_setting_enabled(give_get_option('stripe_receipt_emails'))) {
            $intent_args['receipt_email'] = $donation->email;
        }

        $intent = give(PaymentIntent::class)->create($intent_args);

        DonationNote::create([
            'donationId' => $donation->id,
            'content' => sprintf(__('Stripe Charge/Payment Intent ID: %s', 'give'), $intent->id())
        ]);

        DonationNote::create([
            'donationId' => $donation->id,
            'content' => sprintf(__('Stripe Payment Intent Client Secret: %s', 'give'), $intent->clientSecret())
        ]);

        give_update_meta(
            $donation->id,
            '_give_stripe_payment_intent_client_secret',
            $intent->clientSecret()
        );

        if ('requires_action' === $intent->status()) {
            DonationNote::create([
                'donationId' => $donation->id,
                'content' => __('Stripe requires additional action to be fulfilled. Check your Stripe account.', 'give')
            ]);

            give_update_meta(
                $donation->id,
                '_give_stripe_payment_intent_require_action_url',
                $intent->nextActionRedirectUrl()
            );
        }

        return $intent;
    }
}
