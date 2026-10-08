<?php

namespace Give\PaymentGateways\Gateways\Stripe\Actions;

use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\PaymentGateways\Gateways\Stripe\Exceptions\PaymentMethodException;
use Give\PaymentGateways\Gateways\Stripe\ValueObjects\PaymentMethod;

class GetPaymentMethodFromRequest
{
    /**
     * @since TBD Add translators comments.
     * @since 2.19.0
     *
     * @throws PaymentMethodException
     * @throws Exception
     */
    public function __invoke(Donation $donation): PaymentMethod
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- donation request; DonateRoute verifies the signed route for v3 forms and give_process_donation() verifies the donation form nonce for v2 forms.
        if (!isset($_POST['give_stripe_payment_method'])) {
            throw new PaymentMethodException('Payment Method Not Found');
        }

        $paymentMethod = new PaymentMethod(
            give_clean($_POST['give_stripe_payment_method']) // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- same donation request as above; give_clean() unslashes and sanitizes with sanitize_text_field(), and drops serialized data.
        );

        give_update_meta($donation->id, '_give_stripe_source_id', $paymentMethod->id());

        DonationNote::create([
            'donationId' => $donation->id,
            /* translators: %s: Stripe payment method ID */
            'content' => sprintf(__('Stripe Source/Payment Method ID: %s', 'give'), $paymentMethod->id())
        ]);

        return $paymentMethod;
    }
}
