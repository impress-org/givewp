<?php

namespace Give\Tests\Unit\LegacyStripe;

use Give\Tests\TestCase;

/**
 * Checks that reading the payment mode in the legacy Stripe gateway still behaves as
 * give_clean() did for valid requests.
 *
 * @since TBD
 */
final class StripeRequestInputTest extends TestCase
{
    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        $_GET = [];
        $_POST = [];

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testPaymentModeIsReadFromTheUrl(): void
    {
        $_GET['payment-mode'] = 'stripe_checkout';

        $this->assertSame('stripe_checkout', give_stripe_get_payment_mode_from_request());
    }

    /**
     * @since TBD
     */
    public function testMissingPaymentModeIsAnEmptyString(): void
    {
        unset($_GET['payment-mode']);

        $this->assertSame('', give_stripe_get_payment_mode_from_request());
    }

    /**
     * @since TBD
     */
    public function testPaymentModeMatchesGiveCleanForSlashedAndTaggedInput(): void
    {
        $_GET['payment-mode'] = wp_slash("a\\b'c\"d<b>e</b>");

        $this->assertSame(give_clean($_GET['payment-mode']), give_stripe_get_payment_mode_from_request());
    }

    /**
     * @since TBD
     */
    public function testArrayPaymentModeIsAnEmptyString(): void
    {
        $_GET['payment-mode'] = ['stripe'];

        $this->assertSame('', give_stripe_get_payment_mode_from_request());
    }
}
